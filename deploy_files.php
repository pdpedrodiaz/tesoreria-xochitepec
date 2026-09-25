<?php
// Aumentar límites para archivos grandes (223 MB+)
ini_set('memory_limit', '1024M');
ini_set('upload_max_filesize', '500M');
ini_set('post_max_size', '500M');
ini_set('max_execution_time', '600');
ini_set('max_input_time', '600');

$target_dir = __DIR__ . '/sites/default/files/';

// Asegurar que la carpeta exista
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0775, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['backup_file'])) {
    $file = $_FILES['backup_file'];
    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $upload_path = $target_dir . 'backup_temp.' . $file_ext;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("<b style='color:red;'>Error en la subida. Código de error: {$file['error']}</b>");
    }

    echo "<b>Subiendo archivo (223 MB)...</b><br>";
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        echo "<b style='color:green;'>¡Archivo subido con éxito! Extrayendo contenido...</b><br>";
        
        if (strpos($file['name'], '.zip') !== false) {
            $zip = new ZipArchive();
            if ($zip->open($upload_path) === TRUE) {
                $zip->extractTo($target_dir);
                $zip->close();
                unlink($upload_path);
                echo "<h2 style='color:green;'>¡Extracción ZIP completada con éxito en files/!</h2>";
            } else {
                echo "<b style='color:red;'>Error al abrir el archivo ZIP.</b>";
            }
        } else {
            // Manejo de .tar.gz / .tar
            try {
                $phar = new PharData($upload_path);
                $phar->extractTo($target_dir, null, true);
                unlink($upload_path);
                echo "<h2 style='color:green;'>¡Extracción TAR.GZ completada con éxito en files/!</h2>";
            } catch (Exception $e) {
                echo "<b style='color:red;'>Error al extraer TAR.GZ: " . $e->getMessage() . "</b>";
            }
        }
    } else {
        echo "<b style='color:red;'>Error al mover el archivo subido a {$target_dir}</b>";
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carga de Archivos - Tesorería Xochitepec</title>
</head>
<body style="font-family: Arial, sans-serif; padding: 40px; background-color: #f4f4f9;">
    <div style="max-width: 600px; margin: auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2>Cargar respaldo de carpeta <i>files</i> (223 MB)</h2>
        <p>Selecciona tu archivo comprimido (<b>files_backup.tar.gz</b> o <b>.zip</b>) para enviarlo directamente al Volumen persistente de Railway:</p>
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="backup_file" required style="margin-bottom: 20px;"><br>
            <button type="submit" style="padding: 12px 24px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                Subir y Extraer
            </button>
        </form>
    </div>
</body>
</html>