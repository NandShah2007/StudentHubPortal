console.log("Dashboard Loaded");

// Dashboard Card Animation

const cards = document.querySelectorAll(".card");

cards.forEach(card=>{

card.addEventListener("mouseover",()=>{

card.style.transform="translateY(-10px)";
card.style.transition=".3s";

});

card.addEventListener("mouseout",()=>{

card.style.transform="translateY(0px)";

});

});

// Greeting

const hour = new Date().getHours();

let greeting = "";

if(hour<12){

greeting="Good Morning";

}

else if(hour<17){

greeting="Good Afternoon";

}

else{

greeting="Good Evening";

}

document.querySelector("header h1").innerHTML =
greeting + ", Student";