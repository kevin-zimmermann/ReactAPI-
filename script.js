window.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('form');

    if (form) {
        const processFile = "traitement.php";
        getAllUsers(processFile);
        submittedForm(processFile, form);
        checkValueForm(processFile, form);
    }

    function getAllUsers(action) {
        let divContent = fetch(action + '?users=1', {
            method: 'GET',
        })
            .then(response => {
                return response.json()
            })
            .then(data => {
                console.log(data.data)
                let divMaster = document.createElement('div');
                divMaster.setAttribute('id', 'content-users');
                if (divMaster.innerHTML != "") {
                    data.data.forEach(element => {
                        let div = document.createElement('div');
                        let p = document.createElement('p');
                        let button = document.createElement('input');
                        div.setAttribute('class', 'user' + element.id)
                        button.setAttribute("type", "button")
                        button.setAttribute('data-id', element.id);
                        button.setAttribute("name", 'deleteUser');
                        button.value = "Supprimer";
                        p.innerHTML = element.login;
                        div.append(p, button)
                        form.append(div)

                        deleteUser(action);
                        getAllUsers(action);
                    })
                } else {
                    divMaster.innerHTML = "";
                }


            }).catch(error => {
                console.log(error);
            });


        // for(let i= 0; i < data.data.length; i++){


        // }
        // p.innerHTML = data[1];
        // form.append(p);
        // document.body.innerHTML += data

    }

    function deleteUser(action) {
        let deleteButtons = document.querySelectorAll('input[name="deleteUser"]');
        deleteButtons.forEach(button => {
            button.addEventListener('click', event => {
                const userId = button.getAttribute('data-id');
                fetch(action, {
                    method: 'POST', body: JSON.stringify({'deleteUser': userId})
                })
                    .then(response => {
                        return response.json()
                    })
                    .then(data => {
                        event.target.parentNode.remove();
                    })
                    .catch(err => console.error(err))

                // Effectuez les opérations de suppression ou de traitement supplémentaires en utilisant userId
            });
        });
    }

    function checkValueForm(action, form) {
        for (let i = 0; i < form.length; i++) {
            form[i].addEventListener('keyup', (e) => {
                e.preventDefault();
                let p = document.createElement('p');
                if (form[i].name === "login") {
                    if (form[i].value !== '') {
                        p.innerHTML = '';
                        let dataForm = getDataForm(form);

                        fetch(action, {
                            method: 'POST', body: dataForm, headers: {
                                'Content-type': "multipart/form-data; charset=UTF-8",
                            }
                        })
                            .then(response => {
                                return response.json()
                            })
                            .then(data => {
                                p.innerHTML = data.err;
                                form.append(p);


                                // document.body.innerHTML += data;

                            })
                            .catch(error => {
                                console.error(error);
                            });
                    }
                }


            })
        }
    }

    function submittedForm(action, form) {
        const submitButton = document.getElementsByName('valider');
        submitButton[0].addEventListener('click', (e) => {
            e.preventDefault();
            let p = document.createElement('p');
            let dataForm = getDataForm(form);

            fetch(action + '?valider=1', {
                method: 'POST', body: dataForm, headers: {
                    'Content-type': "multipart/form-data; charset=UTF-8",
                }
            })
                .then(response => {
                    return response.json()
                })
                .then(data => {
                    console.log(data)

                })


        })
    }


    function getDataForm(form) {
        let formData = new FormData(form);
        let object = {};

        formData.forEach((value, key) => object[key] = value);
        let json = JSON.stringify(object);
        return json;
    }

})



