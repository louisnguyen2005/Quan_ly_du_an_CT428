document.addEventListener("DOMContentLoaded", () => {
    const filter = document.getElementById("notificationFilter");
    const cards = Array.from(document.querySelectorAll(".notification-card[data-type]"));

    function applyFilter() {
        if (!filter) return;
        const value = (filter.value || "all").toLowerCase();
        cards.forEach(card => {
            const type = (card.dataset.type || "system").toLowerCase();
            card.style.display = (value === "all" || type === value) ? "flex" : "none";
        });
    }

    cards.forEach(card => {
        card.addEventListener("click", () => {
            card.dataset.read = "1";
            card.classList.add("read");
            card.classList.remove("unread");
        });
    });

    if (filter) {
        filter.addEventListener("change", applyFilter);
        applyFilter();
    }
});
