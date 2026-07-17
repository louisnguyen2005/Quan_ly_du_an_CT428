document.addEventListener("DOMContentLoaded", () => {

    const modal =
        document.getElementById(
            "permissionModal"
        );

    const openBtn =
        document.getElementById(
            "openPermissionModal"
        );

    const closeBtn =
        document.querySelector(
            ".close-permission-modal"
        );

    document
    .querySelectorAll(".user-radio")
    .forEach(radio => {

        radio.addEventListener(
            "change",
            () => {

                document
                .querySelectorAll(".user-card")
                .forEach(card => {

                    card.classList.remove(
                        "selected"
                    );

                });

                radio
                .closest(".user-card")
                .classList.add(
                    "selected"
                );

            }
        );

    });

    openBtn.addEventListener(
        "click",
        () => {

            const selected =
                document.querySelector(
                    ".user-radio:checked"
                );

            if(!selected){

                alert(
                    "Vui lòng chọn người dùng"
                );

                return;
            }

            document.getElementById(
                "editUserId"
            ).value =
                selected.dataset.id;

            document.getElementById(
                "editUserName"
            ).value =
                selected.dataset.name;

            document.getElementById(
                "editUserEmail"
            ).value =
                selected.dataset.email;

            document.getElementById(
                "editUserRole"
            ).value =
                selected.dataset.roleid;

            modal.style.display =
                "flex";

        }
    );

    closeBtn.onclick = () => {

        modal.style.display =
            "none";

    };

    window.onclick = e => {

        if(e.target === modal){

            modal.style.display =
                "none";

        }

    };

});