<?php

require_once 'DB.php';

class Tools
{
    public static function generateUUID()
    {
        $data = random_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return bin2hex($data);
    }

    public static function saveFile()
    {
        // Retourne le nom du fichier si l'enregistrement a réussi, faux sinon.

        $name = self::generateUUID() . '.' . pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

        if (move_uploaded_file($_FILES['file']['tmp_name'], 'files/' . $name)) {
            return DB::clean($name);
        }

        return false;
    }

    public static function saveImage()
    {
        // Vérification des données de l'image, puis enregistrement.
        // Retourne Faux si l'image n'en est pas une, ou si elle n'a pas pu être enregistrée.

        if (!isset($_FILES['file']) || $_FILES['file']['tmp_name'] === '') {
            return false;
        }

        // Vérifie le type MIME avec finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($_FILES['file']['tmp_name']);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($mimeType, $allowedTypes)) {
            return false;
        }

        // On s'assure que l'extension du fichier ne causerait pas de problèmes
        return self::saveFile();
    }


    public static function deleteFile($fileName)
    {

        if (file_exists('files/' . $fileName)) {
            unlink('files/' . $fileName);
            return true;
        }

        return false;
    }


    public static function methodAccepted(...$acceptedContentType)
    {
        // On récupère le Content-Type de la requête (chaine vide si non précisé)
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        // Si le Content-Type de la requête est dans la liste des types acceptés, on retourne vrai
        foreach ($acceptedContentType as $type) {
            if (str_starts_with($contentType, $type)) {
                return true;
            }
        }

        // Erreur si le Content-Type n'est pas supporté
        http_response_code(415);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Unsupported Media Type',
            'message' => "Content-Type '{$contentType}' is not supported. Accepted types: " . implode(', ', $acceptedContentType)
        ]);

        // On arrête le script
        exit;
    }

    public static function hasPermission($permission): bool
    {

        if (!isset($_SESSION['userid'])) {
            return false;
        }

        $db = new DB();
        $perms = $db->select("SELECT * FROM LISTE_PERMISSIONS WHERE id_membre = ?", 'i', [$_SESSION['userid']]);

        if (count($perms) == 0 || !isset($perms[0][$permission]) || $perms[0][$permission] == 0) {
            return false;
        }

        return true;
    }

    public static function checkPermission($permission): void
    {
        if (self::hasPermission($permission) === false) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Forbidden',
                'message' => 'You do not have permission to access this resource.'
            ]);
            exit;
        }
    }
}
