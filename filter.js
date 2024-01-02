window.addEventListener('DOMContentLoaded', () => {

    // const form = document.getElementById('form');
    const inputs = document.getElementsByTagName('input');
    const filter = document.getElementById('filter');
    const URL = window.location.origin + window.location.pathname;
    console.log(URL)

    console.log(inputs)

    //On regarde si notre page contient des inputs
    if (inputs) {
        const processFile = "traitementFilter.php";
        getAllProductsWithFilter(processFile);
    }

    // Cette fonction va nous permettre de récupérer tous les produits + avec les filtres en BDD (cf: traitement.php)
    function getAllProductsWithFilter(action) {
        for (input of inputs) {

            //Mettre sur écoute les inputs en fonction de l'event "change"
            input.addEventListener("change", (e) => {

                e.preventDefault();
                let checkedCheckboxes = document.querySelectorAll("input:checked");
                let priceInputs = document.querySelectorAll("input[type='number']");

                //URLSearchParams définit des méthodes utilitaires pour travailler avec la chaîne de requête (les paramètres GET) d’une URL.
                let params = new URLSearchParams();

                //Ajouter dans l'URL le paramètre de recherche (ex: ?prix[]=1000)
                checkedCheckboxes.forEach(checkedCheckboxe => {
                    params.append(checkedCheckboxe.name, checkedCheckboxe.value);
                })


                priceInputs.forEach(priceInput => {
                    if(priceInput.value !== ""){
                        params.append(priceInput.name, priceInput.value);
                    }

                })

                const query = "?" + params.toString();

                fetch(action + query, {

                    //On utilise cette method car nous cherchons à récupérer une information
                    method: 'GET',
                })
                    .then(response => {
                        return response.json()
                    })
                    .then(data => {
                        history.pushState('', '', URL + query)
                        const existedDiv = document.getElementById('content-products');
                        //supprime la div si elle est déjà existante
                        if (existedDiv) {
                            existedDiv.remove();
                        }
                        // // Création du bloc en HTML qui contiendra les logins de tous les utilisateurs
                        let divMaster = document.createElement('div');
                        divMaster.setAttribute('id', 'content-products');

                        data.products.forEach(element => {
                            let div = document.createElement('div');
                            let p = document.createElement('p');
                            div.setAttribute('class',  element.id)
                            p.innerHTML = element.name + " - " + element.price + "€"  ;
                            div.append(p)
                            divMaster.append(div)
                            filter.after(divMaster)
                        })

                    }).catch(error => {
                    console.log(error);
                });


            })


        }


    }

})



