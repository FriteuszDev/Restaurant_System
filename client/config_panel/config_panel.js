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

///////////////////////////////////////////////////////////////




let fire_icons = document.querySelectorAll(".fa-user-minus")
let all_employees_ids = document.querySelectorAll('.td_employees_id');

fire_icons.forEach((fire, fire_i) => {
    fire.addEventListener("click", () => {
        let EmployeeIdValue = all_employees_ids[fire_i].textContent;
        console.log(EmployeeIdValue)

        const formData = new FormData();
        formData.append('emID', EmployeeIdValue);

    fetch('../../php/delete_employee.php', {
        method: 'POST',
        body: formData
    }).then(response => {
        if (response.ok) {
            location.reload();
        }
    });
    })
})











let grade_delete_icon = document.querySelectorAll(".fa-trash-can")
let all_grades_ids = document.querySelectorAll('.td_grade_id');

grade_delete_icon.forEach((grade, grade_i) => {
    grade.addEventListener("click", () => {
        let GradeIdValue = all_grades_ids[grade_i].textContent;
        console.log(GradeIdValue)

        const formData = new FormData();
        formData.append('gradeID', GradeIdValue);

    fetch('../../php/delete_grade.php', {
        method: 'POST',
        body: formData
    }).then(response => {
        if (response.ok) {
            location.reload();
        }
    });
    })
})












////////////////////////////////////////////////////////////////




let tab_menu = document.querySelector(".tab_menu")
let tab_employees = document.querySelector(".tab_employees")
let tab_tables = document.querySelector(".tab_tables")
let tab_grades = document.querySelector(".tab_grades")

let config_menu = document.querySelector(".config_menu")
let config_employees = document.querySelector(".config_employees")
let config_tables = document.querySelector(".config_tables")
let config_grades = document.querySelector(".config_grades")

let current_tab = "dishes"

if (current_tab == "dishes"){
    config_menu.style.display = "block"
}

tab_menu.addEventListener("click", () => {
    config_employees.style.display = "none"
    config_tables.style.display = "none"
    config_grades.style.display = "none"
    config_menu.style.display = "block"
})
tab_employees.addEventListener("click", () => {
    config_menu.style.display = "none"
    config_tables.style.display = "none"
    config_grades.style.display = "none"
    config_employees.style.display = "block"
})
tab_tables.addEventListener("click", () => {
    config_menu.style.display = "none"
    config_employees.style.display = "none"
    config_grades.style.display = "none"
    config_tables.style.display = "block"
})
tab_grades.addEventListener("click", () => {
    config_menu.style.display = "none"
    config_employees.style.display = "none"
    config_tables.style.display = "none"
    config_grades.style.display = "block"
})



function CheckBox(clickedCheckbox) {
  if (clickedCheckbox.checked) {
    document.querySelector('.config_auto_change').checked = true;
  }
}

function uncheckAll(mainCheckbox) {
  if (!mainCheckbox.checked) {
    const children = document.querySelectorAll('.auto_change');
    children.forEach(child => {
      child.checked = false;
    });
  }
}