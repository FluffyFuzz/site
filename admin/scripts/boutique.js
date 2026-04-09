import { refreshNavbar } from "./navbar.js";
import { requestGET, requestPUT, requestDELETE, requestPATCH, requestPOST, request } from './ajax.js';
import { showLoader, hideLoader } from "./loader.js";
import { toast } from "./toaster.js";
import { showPropertieSkeleton, hidePropertieSkeleton } from "./propertieskeleton.js";
import { getFullFilepath, openFileDialog } from "./files.js";
import { getToggleStatus, updateToggleStatus } from "./toggle.js";

// Show skeleton
showPropertieSkeleton();

// Get inputs
const prop_image = document.getElementById('prop_image');
const prop_name = document.getElementById('prop_name');
const prop_price = document.getElementById('prop_price');
const prop_xp = document.getElementById('prop_xp');
const prop_categorie = document.getElementById('prop_categorie');
const prop_qte = document.getElementById('prop_qte');
const prop_reductions = document.getElementById('prop_reductions');
const save_btn = document.getElementById('save_btn');
const delete_btn = document.getElementById('delete_btn');
const new_btn = document.getElementById('new_btn');

/**
 * Reloads the navigation bar with event items.
 */
async function fetchData() {

    // Fetch data
    let articles = [];
    try{
        articles = await requestGET('/item.php');
    } catch (error) {
        toast('Erreur lors du chargement des articles.', true);
    }

    // Transform data to navbar items
    return articles.map(article => ({label: article.nom_article, id: article.id_article}));

}

/**
 * Saves the article information.
 *
 * @param {number} id_article - The ID of the article to be saved.
 * @returns {Promise<void>} A promise that resolves when the article is successfully saved.
 */
async function saveArticle(id_article){

    // Show loader
    showLoader();

    // Create data
    const data = {
        name: prop_name.value,
        xp: prop_xp.value,
        stocks: prop_qte.value,
        price: prop_price.value,
        categorie: prop_categorie.value,
        reduction: getToggleStatus(prop_reductions)
    };

    // Send data
    try {
        await requestPUT('/item.php?id=' + id_article.toString(), data);
        toast('Article mis à jour avec succès.');
        selectArticle(id_article);
    } catch (error) {
        toast(error.message, true);
    }

    // Stop loader
    hideLoader();

}

/**
 * Deletes the article from the DB.
*/
async function deleteArticle(id_article){

    // Show loader
    showLoader();

    // Send request
    await requestDELETE(`/item.php?id=${id_article}`);
    
    /// Update navbar
    refreshNavbar(fetchData, selectArticle);

    // Deleted message
    toast('Article supprimé avec succès.');

}

/**
 * Loads and displays event information based on the provided article ID.
 *
 * @param {number} id_article - The ID of the article to be selected.
 * @returns {Promise<void>} A promise that resolves when the article information has been fetched and displayed.
 */
async function selectArticle(id_article, li){

    // Show skeleton
    showPropertieSkeleton();

    // Show loader
    showLoader();

    // Fetch grade information
    const article = await requestGET(`/item.php?id=${id_article}`);

    // Update displayed information
    prop_image.src = await getFullFilepath(article.image_article, '../ressources/default_images/boutique.png');
    prop_name.value = article.nom_article;
    prop_xp.value = article.xp_article;
    prop_qte.value = article.stock_article;
    prop_categorie.value = article.categorie_article;
    prop_price.value = article.prix_article;
    updateToggleStatus(prop_reductions, article.reduction_article);

    // Update save button
    save_btn.onclick = ()=>{
        saveArticle(id_article);
    };

    // Delete button
    delete_btn.onclick = ()=>{
        swal({
            title: "Êtes vous sûr ?",
            text: "Cette action est définitive",
            icon: "warning",
            buttons: true,
            dangerMode: true,
          })
          .then((willDelete) => {
            if (willDelete) {
                deleteArticle(id_article);
            }
          });
    };

    // Update name
    prop_name.onkeyup = ()=>{
        li.textContent = prop_name.value;
    };

    // Update image
    document.getElementById('prop_image_edit').onclick = async ()=>{
        
        // Get file form
        const image = await openFileDialog();

        // Update image src
        const url = URL.createObjectURL(image);
        prop_image.src = url;

        // Show loader
        showLoader();

        // Send data
        try {
            await requestPATCH('/item.php?id=' + id_article.toString(), image);
            toast('Image mis à jour avec succès.');
        } catch (error) {
            toast(error.message, true);
        }

        // Stop loader
        hideLoader();

    };

    // Hide loader
    hideLoader();

    // Hide skeleton
    hidePropertieSkeleton();
    
}

// Handle new article
new_btn.onclick = async ()=>{

    // Show loader
    showLoader();

    // Create new article
    try {
        const id = await requestPOST('/item.php');
        refreshNavbar(fetchData, selectArticle, id);
    } catch (error) {
        toast("Erreur lors de la création de l'article", true);
        hideLoader();
    }

};

// Search bar filtering
document.getElementById('navbar_search').addEventListener('input', function () {
    const query = this.value.toLowerCase();
    for (const li of document.getElementById('content_navbar').children) {
        li.hidden = !li.textContent.toLowerCase().includes(query);
    }
});

// Load navbar
refreshNavbar(fetchData, selectArticle);