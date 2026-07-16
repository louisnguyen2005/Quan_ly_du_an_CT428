const taskModal = document.getElementById("taskModal");
const openTaskModal = document.getElementById("openTaskModal");
const closeTaskModal = document.querySelector(".close-task-modal");

if (openTaskModal && taskModal) {
    openTaskModal.onclick = () => {
        taskModal.style.display = "flex";
    };
}

if (closeTaskModal && taskModal) {
    closeTaskModal.onclick = () => {
        taskModal.style.display = "none";
    };
}

window.addEventListener("click", (e) => {
    if (e.target === taskModal) {
        taskModal.style.display = "none";
    }
});

const taskSearch = document.getElementById("taskSearch");
const taskFilter = document.getElementById("taskFilter");

function filterTasks() {
    const keyword = taskSearch ? taskSearch.value.toLowerCase().trim() : "";
    const statusValue = taskFilter ? taskFilter.value : "all";

    document.querySelectorAll(".task-row-card").forEach((card) => {
        const text = card.innerText.toLowerCase();
        const status = card.dataset.status;

        const matchKeyword = keyword === "" || text.includes(keyword);
        const matchStatus = statusValue === "all" || status === statusValue;

        card.style.display = matchKeyword && matchStatus ? "flex" : "none";
    });
}

if (taskSearch) {
    taskSearch.addEventListener("keyup", filterTasks);
}

if (taskFilter) {
    taskFilter.addEventListener("change", filterTasks);
}

document.addEventListener("DOMContentLoaded", () => {
    const taskItems = document.querySelectorAll(".task-item-clickable");

    taskItems.forEach((item) => {
        item.addEventListener("click", (e) => {
            if (
                e.target.closest("button") ||
                e.target.closest("a") ||
                e.target.closest("select") ||
                e.target.closest("form") ||
                e.target.closest("input") ||
                e.target.closest("textarea")
            ) {
                return;
            }

            const taskId = item.getAttribute("data-id");

            if (taskId) {
                window.location.href = `index.php?page=task-detail&id=${taskId}`;
            }
        });
    });
});
