/* MODAL */
const projectModal =
document.getElementById("projectModal");
const openProjectModal =
document.getElementById("openProjectModal");
const closeProjectModal =
document.querySelector(".close-project-modal");
if(openProjectModal){
    openProjectModal.onclick = () => {
        projectModal.style.display = "flex";
    };
}
if(closeProjectModal){
    closeProjectModal.onclick = () => {
        projectModal.style.display = "none";
    };
}
window.addEventListener("click",(e)=>{
    if(e.target === projectModal){
        projectModal.style.display = "none";
    }
});
/* SEARCH */
const projectSearch =
document.getElementById("projectSearch");
if(projectSearch){
    projectSearch.addEventListener("keyup",()=>{
        const keyword =
        projectSearch.value.toLowerCase();
        document
        .querySelectorAll(".project-card")
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
/* FILTER */
const projectFilter =
document.getElementById("projectFilter");
if(projectFilter){
    projectFilter.addEventListener("change",()=>{
        const value =
        projectFilter.value;
        document
        .querySelectorAll(".project-card")
        .forEach(card=>{
            const status =
            card.dataset.status;
            if(value === "all"){
                card.style.display =
                "block";
            }
            else{
                card.style.display =
                status === value
                ? "block"
                : "none";
            }
        });
    });
}