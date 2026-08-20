let table_all_list = document.querySelectorAll(".table")


table_all_list.forEach((table) => {
    table.addEventListener("click", () => {
        table.classList.toggle("green");
    });
})
