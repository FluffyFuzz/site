import { requestGET, requestDELETE, requestPOST } from './ajax.js';
import { showLoader, hideLoader } from "./loader.js";
import { toast } from "./toaster.js";
import { showPropertieSkeleton, hidePropertieSkeleton } from "./propertieskeleton.js";
import { getFileBucketUrl, openFileDialog } from "./files.js";

// Elements
const main_content = document.getElementById('main_content');

async function updateView(){

    // Show skeleton
    showPropertieSkeleton();
    showLoader();

    // Remove all files
    while (main_content.firstChild)
        main_content.removeChild(main_content.firstChild);

    // Add "upload file" button
    addUpdloadButton();

    // Fetch data
    const data = await requestGET('/accounting.php');

    // Add elements
    for (let i = 0; i < data.length; i++) {
        addAccountingElement(data[i]);
    }

    // Hide skeletons
    hidePropertieSkeleton();
    hideLoader();

}

function addAccountingElement(data){

    // Create div
    const container = document.createElement('div');
    container.className = 'file-element';

    // Add icon
    const icon = document.createElement('img');
    icon.src = '../ressources/sheet.png';
    container.appendChild(icon);

    // Add text
    const text = document.createElement('div');
    const title = document.createElement('p');
    title.textContent = data.nom_comptabilite;
    text.appendChild(title);
    const date = document.createElement('p');
    date.textContent = data.date_comptabilite;
    text.appendChild(date);
    container.appendChild(text);

    // Add buttons
    const download_button = document.createElement('button');
    download_button.className = 'btn-transparent btn-blue';
    download_button.innerHTML = '<img src="../ressources/download.svg">';
    container.appendChild(download_button);
    const delete_button = document.createElement('button');
    delete_button.className = 'btn-transparent btn-red';
    delete_button.innerHTML = '<img src="../ressources/delete.svg">';
    container.appendChild(delete_button);

    // Append div
    main_content.appendChild(container);

    // Add download event
    download_button.onclick = () => {
        window.open(getFileBucketUrl(data.url_comptabilite), '_blank');
    }

    // Add delete event
    delete_button.onclick = async () => {
        swal({
            title: "Êtes-vous sûr ?",
            text: "Voulez-vous vraiment supprimer ce fichier ?",
            icon: "warning",
            buttons: ["Annuler", "Supprimer"],
            dangerMode: true,
        }).then(async (willDelete) => {
            if (willDelete) {
                try {
                    await requestDELETE(`/accounting.php?id=${data.id_comptabilite}`);
                    toast("Fichier supprimé avec succès.");
                    updateView();
                } catch (error) {
                    toast(error.message, true);
                }
            }
        });
        
    }

}

function addUpdloadButton(){

    // Add "upload file" button
    const upload_button = document.createElement('button');
    upload_button.innerHTML = '<img src="../ressources/download.svg"><p>Importer un fichier</p>';
    upload_button.className = 'btn-transparent btn-blue upload-button';
    main_content.appendChild(upload_button);

    // Add event listener
    upload_button.onclick = async () => {

        // Get file
        const file = await openFileDialog("document/*");

        // Ask name
        swal({
            title: "Entrez le nom a donner",
            text: "Veuillez saisir le nom :",
            content: {
              element: "input",
              attributes: {
                placeholder: "Nom du fichier",
                type: "text"
              },
            },
            buttons: ["Annuler", "Valider"],
          }).then(function (value) {
            if (value) {
                uploadFile(file, value, new Date().toISOString().split('T')[0]);
            }
          });

    }

}

async function uploadFile(file, name, date){

    // Show loader
    showLoader();

    // Create form data
    const form_data = new FormData();
    form_data.append('file', file);
    form_data.append('nom', name);
    form_data.append('date', date);

    // Send request
    try{
        await requestPOST('/accounting.php', form_data);
        toast("Fichier uploadé avec succès");
        updateView();
    } catch (error) {
        toast(error.message, true);
        hideLoader();
    }

}

updateView();