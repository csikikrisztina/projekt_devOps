register.addEventListener("click", registerNewUser);
function registerNewUser() {
  fetchCall("register.php", responseUserTableInfo);
  function responseUserTableInfo(data) {
    console.log(data);
    const formDiv = document.createElement("div");
    formDiv.className = "formDiv";
    const h2 = document.createElement("h2");
    h2.textContent = "Create Account";
    formDiv.appendChild(h2);

    const form = document.createElement("form");
    form.className = "register-form";
    const columns = data.columns;
    columns.forEach((column) => {
      const input = document.createElement("input");
      input.name = column.Field;
      input.placeholder = column.Field;
      switch (column.Field) {
        case "password":
          input.type = "password";
          break;
        case "email":
          input.type = "email";
          break;
        default:
          input.type = "text";
          break;
      }
      form.appendChild(input);
    });
    const submit = document.createElement("input");
    submit.type = "submit";
    submit.name = "Register";
    submit.addEventListener('click', registerFormSubmit)
    form.appendChild(submit);
    formDiv.appendChild(form);
    displayOverlay(formDiv);
  }
}
function registerFormSubmit(e){
    e.preventDefault();
    const form=document.querySelector('.register-form');
    const formData = new FormData(form);
    fetchCall('register.php', responseRegisterFormSubmit, 'POST', formData);
    function responseRegisterFormSubmit(data){
       if(data.registration){
        alert("Account created");
        removeOverlay();
       }else{
        alert(data.error);
        form.reset();
       }
    }
}