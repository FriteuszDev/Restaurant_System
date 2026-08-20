let trash = document.querySelectorAll(".fa-trash")
let all_dishes_ids = document.querySelectorAll('.td_menu_id');

trash.forEach((trash, trash_i) => {
    trash.addEventListener("click", () => {
        let dishIdValue = all_dishes_ids[trash_i].textContent;
        console.log(dishIdValue)



        const formData = new FormData();
        formData.append('DishID', dishIdValue);

    fetch('../../php/delete_dish.php', {
        method: 'POST',
        body: formData
    }).then(response => {
        if (response.ok) {
            location.reload();
        }
    });
    })
})



