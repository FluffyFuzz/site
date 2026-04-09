/**
 * Retrieves the full file path for a given filename.
 * If the filename is invalid (empty, null, or "N/A"), returns the default file path.
 * If the file does not exist on the server, returns the default file path.
 *
 * @param {string} filename - The name of the file to retrieve the path for.
 * @param {string} defaultFile - The default file path to return if the filename is invalid or the file does not exist.
 * @returns {Promise<string>} The full file path or the default file path.
 */
export async function getFullFilepath(filename, defaultFile) {
    // Vérifiez si le filename est invalide (vide, null ou "N/A")
    if (!filename || filename === "N/A") {
        return defaultFile;
    }

    const fullFilePath = getFileBucketUrl(filename);
    try {
        const response = await fetch(fullFilePath);
        if (!response.ok) {
            return defaultFile;
        }
        return fullFilePath;
    } catch {
        return defaultFile;
    }
}

/**
 * Opens a file dialog for image selection and returns a FormData object with the selected file.
 * Only accepts image/jpeg, image/png, and image/webp MIME types.
 *
 * @returns {Promise<File|Blob>} A promise that resolves to a FormData object containing the selected file.
 */
export async function openFileDialog(accept = 'image/*') {
    return new Promise((resolve, reject) => {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = accept;

        input.onchange = () => {
            const file = input.files[0];
            if (file) {
                resolve(file);
            } else {
                reject(new Error('No file selected'));
            }
        };

        input.click();
    });
}

/**
 * Retrieves the URL of a file stored in the file bucket.
 * 
 * @param {string} filename - The name of the file to retrieve the URL for.
 * @returns {string} The URL of the file.
 */
export function getFileBucketUrl(filename){
    if (filename && (filename.startsWith('http://') || filename.startsWith('https://'))) {
        return filename;
    }
    return `/api/files/${filename}`;
}