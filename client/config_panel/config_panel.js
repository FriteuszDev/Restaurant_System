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




let all_grade_save_icons = document.querySelectorAll(".fa-floppy-disk")
let all_emp_ids = document.querySelectorAll(".td_employees_id")

const selectedGrades = {};
let all_emp_grade_select = document.querySelectorAll(".emp_grade_select");

all_emp_grade_select.forEach((selectElement, index) => {
    if (selectElement.tagName !== 'SELECT') return;

    const key = selectElement.name || index;
    const selectedOption = selectElement.options?.[selectElement.selectedIndex]; 

    selectedGrades[key] = {
        value: selectElement.value || '',
        text: selectedOption ? selectedOption.text : ''
    };

    selectElement.addEventListener('change', (event) => {
        const target = event.target;
        const opt = target.options?.[target.selectedIndex];

        selectedGrades[key] = {
            value: target.value,
            text: opt ? opt.text : ''
        };
    });

});




all_grade_save_icons.forEach((save_icon, save_icon_i) => {
    save_icon.addEventListener("click", () => {
        let emp_id = all_emp_ids[save_icon_i].textContent;

        let row = save_icon.closest('tr');

        let selectElement = row.querySelector('select');
        let emp_grade_select = selectElement ? selectElement.value : null;

        console.log(`${emp_id} --> ${emp_grade_select}`);

        const formData = new FormData();
        formData.append('emp_id', emp_id);
        formData.append('new_grade_name', emp_grade_select);

            fetch('../../php/save_grade.php', {
            method: 'POST',
            body: formData
        }).then(response => {
            if (response.ok) {
                location.reload();
            }
        });
    });
});







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