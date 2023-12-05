const moreButtons = document.querySelectorAll(".matches__match__more");

moreButtons.forEach((button) => {
  button.addEventListener("click", function (event) {
    event.preventDefault();
    const detailsDiv = this.nextElementSibling;

    if (
      detailsDiv.style.display === "none" ||
      detailsDiv.style.display === ""
    ) {
      detailsDiv.style.display = "block";
      this.textContent = "Close";
    } else {
      detailsDiv.style.display = "none";
      this.textContent = "More";
    }
  });
});

const submitButton = document.querySelector(
  'input[type="submit"][name="compare"]'
);

if (submitButton) {
  submitButton.addEventListener("click", function (event) {
    submitButton.disabled = true;
    submitButton.value = "Submitting...";

    setTimeout(function () {
      form.submit();
    }, 300);

    event.preventDefault();
  });
}
