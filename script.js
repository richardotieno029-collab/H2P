// SAMPLE ROOM DATA
const rooms = [
    {
        id: 1,
        name: "Sunrise Bedsitter",
        area: "Mawenzi",
        price: 4500,
        type: "bedsitter",
        image: "room1.jpg"
    },
    {
        id: 2,
        name: "Quiet Single Room",
        area: "Old Gate",
        price: 3000,
        type: "single",
        image: "room2.jpg"
    },
    {
        id: 3,
        name: "1 Bedroom Apartment",
        area: "Annex",
        price: 7000,
        type: "onebed",
        image: "room3.jpg"
    }
];

// ------------ FAVORITES SYSTEM (LOCAL STORAGE) ------------
let favorites = JSON.parse(localStorage.getItem("favs")) || [];

function toggleFavorite(id) {
    if (favorites.includes(id)) {
        favorites = favorites.filter(fav => fav !== id);
    } else {
        favorites.push(id);
    }
    localStorage.setItem("favs", JSON.stringify(favorites));
    displayRooms();
}

// ------------ ROOM DISPLAY FUNCTION ------------
function displayRooms() {
    const container = document.getElementById("rooms-container");
    container.innerHTML = "";

    rooms.forEach(room => {
        const favActive = favorites.includes(room.id) ? "active" : "";
        container.innerHTML += `
            <div class="room-card">
                <img src="${room.image}" alt="${room.name}">
                
                <div class="room-info">
                    <button class="favorite-btn ${favActive}" onclick="toggleFavorite(${room.id})">❤</button>
                    <h3>${room.name}</h3>
                    <p><strong>Area:</strong> ${room.area}</p>
                    <p><strong>Type:</strong> ${room.type}</p>
                    <p><strong>Price:</strong> KES ${room.price}</p>
                </div>
            </div>
        `;
    });
}

displayRooms();