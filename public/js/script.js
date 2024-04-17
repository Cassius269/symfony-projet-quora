console.log('hello wonder');

//Sélection de la nav de l'aiside
let navAside = document.querySelector("nav");

console.log(navAside);

/*
selectionner la nav qui est parent
Event
Si est un bouton de classe "asideNavButton" alors ajouter la classe avec toggle */

navAside.addEventListener('click', (e)=>{
    console.log(e.target);
console.log(e)
    if(e.target.tagName==="BUTTON" && e.target.classList.contains("asideNavButton")){
        console.log("Je suis un bouton asideNavButton");

        e.target.classList.toggle("navButtonActive");
    }

})