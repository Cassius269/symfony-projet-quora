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
    if((e.target.tagName==="BUTTON" &&  e.target.classList.contains("asideNavButton")) ){
            
        console.log("Je suis un button asideNavButton");
        e.target.classList.toggle("navButtonActive");
        e.target.classList.toggle("leftbarre");

    }else if (e.target.tagName==="IMG" &&  e.target.parentNode.parentNode.classList.contains("asideNavButton")){
        
        console.log("Je suis une balise image de la liste d'option");
        e.target.parentNode.parentNode.classList.toggle("navButtonActive");
        e.target.parentNode.parentNode.classList.toggle("leftbarre");

    }else if(e.target.tagName==='A' && e.target.parentNode.classList.contains("asideNavButton")){
        console.log('Je suis une balise <a>');
        e.target.parentNode.classList.toggle("navButtonActive");
        e.target.parentNode.classList.toggle("leftbarre");
    }

})