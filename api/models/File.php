<?php


require_once __DIR__ . '/BaseModel.php';


class File implements JsonSerializable
{
    private string $fileName;

    public function getFileName(): string
    {
        return $this->fileName;
    }


    private function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }

    public static function getFile(string | null $fileName): File | null
    {
        if (!is_null($fileName) && $fileName !== '' && file_exists('files/' . $fileName)) {
            return new File($fileName);
        }

        return null;
    }


    // Non, je ne souhaite pas expliquer ce code.
    // Il a été (honnetement) généré via Claude (ia) car PHP refuse de
    // mettre les fichiers dans $_FILES si la requête n'est pas un POST.
    // Or, on utilise PUT et PATCH pour les fichiers.
    // Au moment d'écrire ces lignes, je suis vraiment enervé contre PHP.
    // Villain php
    public static function saveFile(): File | null
    {
        $method = $_SERVER['REQUEST_METHOD'];

        // Gestion des requêtes POST (formulaires classiques)
        if ($method === 'POST') {
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                return null;
            }

            $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $name = tools::generateUUID() . '.' . $extension;

            if (move_uploaded_file($_FILES['file']['tmp_name'], 'files/' . $name)) {
                chmod('files/' . $name, 0644);
                return new File($name);
            }
            return null;
        }

        // Gestion des requêtes PUT/PATCH
        if ($method === 'PUT' || $method === 'PATCH') {
            // Lecture du corps de la requête
            $putData = fopen('php://input', 'r');

            // Création d'un fichier temporaire
            $tempFile = tempnam(sys_get_temp_dir(), 'upload_');

            // S'assurer que le fichier temporaire est créé avec les bonnes permissions
            chmod($tempFile, 0644);

            $tempHandle = fopen($tempFile, 'w');

            // Copie des données
            stream_copy_to_stream($putData, $tempHandle);

            // Fermeture des flux
            fclose($putData);
            fclose($tempHandle);

            // Détection du type de fichier
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($tempFile);

            // Détermination de l'extension basée sur le type MIME
            $extensions = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
                'application/pdf' => 'pdf',
                # Excel
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
                'application/vnd.ms-excel' => 'xls',
            ];

            $extension = $extensions[$mimeType] ?? 'bin';
            $name = tools::generateUUID() . '.' . $extension;

            // Déplacement du fichier vers sa destination finale
            if (rename($tempFile, 'files/' . $name)) {
                return new File($name);
            }

            // Nettoyage en cas d'échec
            @unlink($tempFile);
            return null;
        }

        return null;
    }

    // cf. mon commentaire de la méthode ci dessus
    public static function saveImage(): File | null
    {
        // Types MIME autorisés
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        // Lecture du corps brut de la requête
        $rawData = file_get_contents('php://input');
        if (!$rawData) {
            return null; // Pas de données brutes
        }

        // Création d'un fichier temporaire pour analyser l'image
        $tmpFile = tempnam(sys_get_temp_dir(), 'upload_');
        file_put_contents($tmpFile, $rawData);

        // Vérification du type MIME
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($tmpFile);
        if (!in_array($mimeType, $allowedTypes)) {
            unlink($tmpFile); // Nettoyage
            return null; // Type non autorisé
        }

        // Appel de la méthode `saveFile` avec le fichier temporaire
        $savedFile = self::saveFile();

        // Nettoyage du fichier temporaire après enregistrement
        unlink($tmpFile);

        return $savedFile; // Retourne l'objet File ou null si l'enregistrement échoue
    }


    public function deleteFile(): bool
    {
        if (file_exists('files/' . $this->fileName)) {
                unlink('files/' . $this->fileName);
                return true;
        }

            return false;
    }

    public function __toString(): string
    {
        return $this->fileName;
    }


    public function jsonSerialize(): string
    {
        return $this->fileName;
    }
}
