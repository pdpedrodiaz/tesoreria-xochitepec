<?php
// Aumentar límites para el procesamiento de la Base de Datos
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '900');

$db_host = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: '127.0.0.1';
$db_port = getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: '3306';$db_user = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
$db_pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASSWORD') ?: '';$db_name = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'railway';

$target_dir = __DIR__ . '/sites/default/files/';
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0775, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    // 1. SUBIR ARCHIVO, CONTAR TABLAS Y LIMPIAR BASE DE DATOS
    if ($_POST['action'] === 'upload_dump') {
        if (isset($_FILES['db_file']) &&$_FILES['db_file']['error'] === UPLOAD_ERR_OK) {
            $dump_path =$target_dir . 'database_backup.sql';
            $ext = strtolower(pathinfo($_FILES['db_file']['name'], PATHINFO_EXTENSION));

            if ($ext === 'gz') {
                $gz = gzopen($_FILES['db_file']['tmp_name'], 'rb');
                $out = fopen($dump_path, 'wb');
                while (!gzeof($gz)) {
                    fwrite($out, gzread($gz, 524288)); // Búfer de 512KB para descompresión
                }
                fclose($out);
                gzclose($gz);
            } else {
                move_uploaded_file($_FILES['db_file']['tmp_name'],$dump_path);
            }

            // Contar sentencias CREATE TABLE para determinar el total de tablas
            $total_tables = 0;
            $table_names = [];
            $handle = fopen($dump_path, 'r');
            if ($handle) {
                while (($line = fgets($handle, 1048576)) !== false) {
                    if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`"]?([a-zA-Z0-9_]+)[`"]?/i', $line, $matches)) {$total_tables++;
                        $table_names[] =$matches[1];
                    }
                }
                fclose($handle);
            }

            // Limpieza previa de tablas en MariaDB
            $mysqli = new mysqli($db_host, $db_user,$db_pass, $db_name, (int)$db_port);
            if (!$mysqli->connect_error) {$mysqli->query("SET FOREIGN_KEY_CHECKS = 0;");
                $result =$mysqli->query("SHOW TABLES");
                while ($row = $result->fetch_row()) {$mysqli->query("DROP TABLE IF EXISTS `" . $row[0] . "`");
                }
                $mysqli->query("SET FOREIGN_KEY_CHECKS = 1;");
                $mysqli->close();
            }

            echo json_encode([
                'success' => true,
                'fileSize' => filesize($dump_path),
                'totalTables' => $total_tables,
                'tableList' => $table_names
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al subir el archivo SQL.']);
        }
        exit;
    }

    // 2. PROCESAMIENTO POR LOTES CON DETECCIÓN DE TABLA ACTUAL
    if ($_POST['action'] === 'process_batch') {
        $offset = intval($_POST['offset']);
        $current_table =$_POST['currentTable'] ?? 'Iniciando...';
        $created_tables_count = intval($_POST['createdTablesCount'] ?? 0);
        $dump_path =$target_dir . 'database_backup.sql';

        if (!file_exists($dump_path)) {
            echo json_encode(['success' => false, 'error' => 'No se encuentra el archivo SQL subido.']);
            exit;
        }

        $mysqli = new mysqli($db_host, $db_user,$db_pass, $db_name, (int)$db_port);
        if ($mysqli->connect_error) {
            echo json_encode(['success' => false, 'error' => 'Error de conexión a MariaDB: ' . $mysqli->connect_error]);
            exit;
        }
        $mysqli->set_charset("utf8mb4");
        
        // Ajustar límites de sesión de MariaDB para soportar paquetes y blobs gigantes
        $mysqli->query("SET SESSION max_allowed_packet = 1073741824;"); // 1GB
        $mysqli->query("SET FOREIGN_KEY_CHECKS = 0;");
        $mysqli->query("SET UNIQUE_CHECKS = 0;");
        $mysqli->query("SET AUTOCOMMIT = 0;");

        $file = fopen($dump_path, 'rb');
        fseek($file,$offset);

        $query = '';$queries_executed = 0;
        $max_queries_per_batch = 250; 
        $errors = [];

        while (!feof($file) && $queries_executed <$max_queries_per_batch) {
            // Lectura con búfer amplio de 1MB por línea para evitar trucamiento de blobs/inserts gigantes
            $line = fgets($file, 1048576); 
            if ($line === false) break;

            $trimmed_line = trim($line);

            // Rastrear la tabla actual
            if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`"]?([a-zA-Z0-9_]+)[`"]?/i', $line, $matches)) {$current_table = $matches[1];$created_tables_count++;
            } elseif (preg_match('/INSERT\s+INTO\s+[`"]?([a-zA-Z0-9_]+)[`"]?/i', $line,$matches)) {
                $current_table =$matches[1];
            }

            // Ignorar comentarios
            if (substr($trimmed_line, 0, 2) == '--' || substr($trimmed_line, 0, 1) == '#' \vert{}\vert{} $trimmed_line == '') {
                continue;
            }

            $query .=$line;
            if (substr($trimmed_line, -1) == ';') {
                if (!$mysqli->query($query)) {
                    // Guardar advertencia en el log si hay error en la consulta pero continuar
                    $errors[] = "Error en tabla '$current_table': " . substr($mysqli->error, 0, 150);
                }
                $query = '';$queries_executed++;
            }
        }

        $mysqli->query("COMMIT;");
        $new_offset = ftell($file);
        $is_eof = feof($file);
        fclose($file);$mysqli->close();

        if ($is_eof) {
            @unlink($dump_path);
        }

        echo json_encode([
            'success' => true,
            'newOffset' => $new_offset,
            'isEof' => $is_eof,
            'currentTable' => $current_table,
            'createdTablesCount' => $created_tables_count,
            'errors' => $errors
        ]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Importador SQL Avanzado con Contador de Tablas</title>
    <style>
        body { font-family: Segoe UI, Arial, sans-serif; padding: 30px; background: #eef2f5; color: #333; }
        .card { max-width: 750px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 20px; text-align: center; }
        .stat-box { background: #f8f9fa; padding: 15px; border-radius: 6px; border: 1px solid #e0e0e0; }
        .stat-number { font-size: 22px; font-weight: bold; color: #007bff; margin-top: 5px; }
        .progress-container { width: 100%; background: #e0e0e0; border-radius: 6px; overflow: hidden; margin-top: 20px; display: none; }
        .progress-bar { width: 0%; height: 28px; background: #007bff; text-align: center; color: white; line-height: 28px; font-weight: bold; transition: width 0.15s; }
        #console-log { margin-top: 20px; background: #1e1e1e; color: #00ff66; font-family: monospace; padding: 15px; border-radius: 6px; height: 160px; overflow-y: auto; font-size: 13px; display: none; }
        button { padding: 12px 24px; background: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; margin-top: 15px; }
        button:disabled { background: #6c757d; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Importador de Base de Datos por Lotes</h2>
        <p>Procesamiento optimizado para volcados extensos de Drupal 7 con reporte continuo de tablas.</p>
        
        <input type="file" id="sqlFile" accept=".sql,.gz" style="width: 100%;"><br>
        <button id="startBtn" onclick="startImport()">Limpiar Base de Datos e Importar</button>

        <div class="stats-grid" id="statsGrid" style="display:none;">
            <div class="stat-box">
                <div>Tabla Actual</div>
                <div class="stat-number" id="statCurrentTable" style="font-size: 15px; word-break: break-all;">-</div>
            </div>
            <div class="stat-box">
                <div>Tablas Creadas</div>
                <div class="stat-number" id="statCreatedTables">0 / 0</div>
            </div>
            <div class="stat-box">
                <div>Tablas Faltantes</div>
                <div class="stat-number" id="statPendingTables" style="color: #dc3545;">0</div>
            </div>
        </div>

        <div class="progress-container" id="progressContainer">
            <div class="progress-bar" id="progressBar">0%</div>
        </div>

        <div id="console-log"></div>
    </div>

    <script>
    function logMessage(msg) {
        const consoleLog = document.getElementById('console-log');
        consoleLog.style.display = 'block';
        consoleLog.innerHTML += '<div>> ' + msg + '</div>';
        consoleLog.scrollTop = consoleLog.scrollHeight;
    }

    async function startImport() {
        const fileInput = document.getElementById('sqlFile');
        const file = fileInput.files[0];
        if (!file) {
            alert('Selecciona un archivo .sql o .sql.gz');
            return;
        }

        if (!confirm('¿Deseas vaciar la base de datos e iniciar la importación por lotes?')) {
            return;
        }

        const startBtn = document.getElementById('startBtn');
        const progressContainer = document.getElementById('progressContainer');
        const progressBar = document.getElementById('progressBar');
        const statsGrid = document.getElementById('statsGrid');

        startBtn.disabled = true;
        progressContainer.style.display = 'block';
        statsGrid.style.display = 'grid';
        logMessage('Subiendo archivo SQL y analizando estructura de tablas...');

        const formData = new FormData();
        formData.append('action', 'upload_dump');
        formData.append('db_file', file);

        try {
            const uploadRes = await fetch('import_db.php', { method: 'POST', body: formData });
            const uploadData = await uploadRes.json();

            if (!uploadData.success) {
                logMessage('<span style="color:red;">❌ Error al subir: ' + uploadData.error + '</span>');
                startBtn.disabled = false;
                return;
            }

            const totalSize = uploadData.fileSize;
            const totalTables = uploadData.totalTables;
            let currentOffset = 0;
            let currentTable = 'Iniciando...';
            let createdTablesCount = 0;

            logMessage('Estructura detectada: ' + totalTables + ' tablas en el respaldo.');
            logMessage('Base de datos previa limpiada. Procesando lotes SQL...');

            while (true) {
                const batchData = new FormData();
                batchData.append('action', 'process_batch');
                batchData.append('offset', currentOffset);
                batchData.append('currentTable', currentTable);
                batchData.append('createdTablesCount', createdTablesCount);

                const batchRes = await fetch('import_db.php', { method: 'POST', body: batchData });
                const batchResult = await batchRes.json();

                if (!batchResult.success) {
                    logMessage('<span style="color:red;">❌ Error: ' + batchResult.error + '</span>');
                    startBtn.disabled = false;
                    return;
                }

                currentOffset = batchResult.newOffset;
                currentTable = batchResult.currentTable;
                createdTablesCount = batchResult.createdTablesCount;

                // Actualizar contadores en pantalla
                document.getElementById('statCurrentTable').innerText = currentTable;
                document.getElementById('statCreatedTables').innerText = createdTablesCount + ' / ' + totalTables;
                
                const pending = Math.max(0, totalTables - createdTablesCount);
                document.getElementById('statPendingTables').innerText = pending;

                // Mostrar avisos no-fatales si los hubo
                if (batchResult.errors && batchResult.errors.length > 0) {
                    batchResult.errors.forEach(err => logMessage('<span style="color:yellow;">⚠️ ' + err + '</span>'));
                }

                const percent = Math.min(100, Math.round((currentOffset / totalSize) * 100));
                progressBar.style.width = percent + '%';
                progressBar.innerText = percent + '%';

                if (batchResult.isEof || percent >= 100) {
                    progressBar.style.background = '#28a745';
                    document.getElementById('statPendingTables').innerText = '0';
                    document.getElementById('statPendingTables').style.color = '#28a745';
                    logMessage('<span style="color:#00ff66;">🎉 ¡Importación completada con éxito al 100%!</span>');
                    break;
                }
            }
        } catch (err) {
            logMessage('<span style="color:red;">❌ Error de red o interrupción del servidor.</span>');
            startBtn.disabled = false;
        }
    }
    </script>
</body>
</html>
