const userModal =
document.getElementById("userModal");
const openUserModal =
document.getElementById("openUserModal");
const closeUserModal =
document.querySelector(".close-modal");
if(openUserModal){
    openUserModal.onclick = () => {
        userModal.style.display = "flex";
    };
}
if(closeUserModal){
    closeUserModal.onclick = () => {
        userModal.style.display = "none";
    };
}
window.onclick = (e) => {
    if(e.target === userModal){
        userModal.style.display = "none";
    }
};
/* SEARCH */
const searchInput =
document.getElementById("searchUser");
if(searchInput){
    searchInput.addEventListener("keyup",()=>{
        const keyword =
        searchInput.value.toLowerCase();
        document
        .querySelectorAll(".user-card")
        .forEach(card=>{
            const text =
            card.innerText.toLowerCase();
            card.style.display =
            text.includes(keyword)
            ? "block"
            : "none";
        });
    });
}
/* ROLE FILTER */
const roleFilter =
document.getElementById("roleFilter");
if(roleFilter){
    roleFilter.addEventListener("change",()=>{
        const value =
        roleFilter.value;
        document
        .querySelectorAll(".user-card")
        .forEach(card=>{
            const role =
            card.dataset.role;
            if(value === "all"){
                card.style.display =
                "block";
            }
            else{
                card.style.display =
                role === value
                ? "block"
                : "none";
            }
        });
    });
}
// Thêm đoạn này vào dưới cùng file users.js của bạn
const btnCancelModal = document.getElementById("btnCancelModal");
if(btnCancelModal){
    btnCancelModal.onclick = () => {
        userModal.style.display = "none";
    };
}