//services
document.addEventListener("DOMContentLoaded", function () {
  const requestButtons = document.querySelectorAll(".request-btn");
  const documentTypeInput = document.getElementById("document_type");
  const modalTitle = document.getElementById("documentRequestModalLabel");

  requestButtons.forEach(button => {
    button.addEventListener("click", function () {
      const docType = this.getAttribute("data-document");
      documentTypeInput.value = docType;
      modalTitle.textContent = "Request " + docType;
    });
  });
});

//modal documents
document.addEventListener("DOMContentLoaded", function () {
  const birthdateInput = document.getElementById("birthdate");
  const ageInput = document.getElementById("age");

  function calculateAge() {
    const birthdate = new Date(birthdateInput.value);
    if (!isNaN(birthdate)) {
      const today = new Date();
      let age = today.getFullYear() - birthdate.getFullYear();
      const monthDiff = today.getMonth() - birthdate.getMonth();
      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
        age--;
      }
      ageInput.value = age >= 0 ? age : "";
    } else {
      ageInput.value = "";
    }
  }

  // Calculate on change
  birthdateInput.addEventListener("change", calculateAge);

  // Calculate immediately if there's already a value
  if (birthdateInput.value) {
    calculateAge();
  }
});

