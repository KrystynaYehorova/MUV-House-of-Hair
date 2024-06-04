document.addEventListener("DOMContentLoaded", function () {
  // Tutaj umieść swój kod, który będzie wykonywany po załadowaniu całej strony

  const addToCartButtons = document.querySelectorAll(".shop-item-button");
  addToCartButtons.forEach((button) => {
    button.addEventListener("click", addToCartClicked);
  });

  const removeCartItemButtons = document.querySelectorAll(".btn-danger");
  removeCartItemButtons.forEach((button) => {
    button.addEventListener("click", removeCartItem);
  });

  const quantityInputs = document.querySelectorAll(".cart-quantity-input");
  quantityInputs.forEach((input) => {
    input.addEventListener("change", quantityChanged);
  });

  const purchaseButton = document.querySelector(".btn-purchase");
  purchaseButton.addEventListener("click", openModalWithCartContent);

  // Dodanie obsługi przycisku "Zarezerwuj" w drugim modalu
  const btnRezervModal = document.getElementById("btn-rezerv-mailer");
  btnRezervModal.addEventListener("click", handleReservation);
});

async function handleReservation() {
  // Pobierz dane do wysłania
  const firstName = document.getElementById("first-name").value;
  const lastName = document.getElementById("last-name").value;
  const email = document.getElementById("floatingInput").value;

  if (!firstName || !lastName || !email) {
    alert("Proszę wypełnić wszystkie wymagane pola!");
    return;
  }

  const cartItemsHTML = document.querySelector("#modal-cart-items").innerHTML;
  const cartTotalPriceHTML = document.querySelector(
    "#modal-cart-total-price"
  ).innerText;

  // Utwórz obiekt FormData
  const formData = new FormData();
  formData.append("firstName", firstName);
  formData.append("lastName", lastName);
  formData.append("email", email);
  formData.append("cartItemsHTML", cartItemsHTML);
  formData.append("cartTotalPriceHTML", cartTotalPriceHTML);

  // Wyślij dane do serwera za pomocą funkcji sendFormData
  await sendFormData(formData);
}

async function sendFormData(formData) {
  try {
    const response = await fetch("sendmail.php", {
      method: "POST",
      body: formData,
    });

    if (!response.ok) {
      throw `Błąd podczas wysyłania danych na serwer: ${response.status}`;
    }

    const json = await response.json();
    if (json.result === "success") {
      alert("Dane zostały pomyślnie wysłane na serwer!");
      window.location.href = "https://muvhouseofhair.pl";
    } else {
      alert("Błąd na serwerze: " + json.message);
    }
  } catch (error) {
    alert("Błąd podczas wysyłania danych: " + error);
  }
}

async function openModalWithCartContent() {
  const cartItems = document.querySelector(".cart-items").innerHTML;
  const cartTotalPrice = document.querySelector(".cart-total-price").innerText;
  const isDeliveryChecked = document.getElementById("deliveryCheckbox").checked;

  // Ustawienie zawartości koszyka w drugim modalu
  document.querySelector("#modal-cart-items").innerHTML = cartItems;

  const removeButtons = document.querySelectorAll(
    "#modal-cart-items .btn-delete"
  );
  removeButtons.forEach((button) => {
    button.style.display = "none";
  });

  // Dodanie pola dostawy, jeśli została zaznaczona opcja dostawy
  if (isDeliveryChecked) {
    const deliveryRow = document.createElement("tr");
    deliveryRow.innerHTML = `
      <td>Dostawa</td>
      <td>19 zł</td>
    `;
    document.querySelector("#modal-cart-items").appendChild(deliveryRow);
  }

  // Ustawienie całkowitej ceny w drugim modalu
  document.querySelector("#modal-cart-total-price").innerText = cartTotalPrice;

  // Ustawienie całkowitej ceny w stopce drugiego modalu
  document.querySelector(
    "#modal-cart-footer"
  ).innerText = `Do zapłaty: ${cartTotalPrice}`;

  // Dodanie obsługi przycisku "Zarezerwuj"
  const btnRezervModal = document.getElementById("btn-rezerv-mailer");
  btnRezervModal.addEventListener("click", handleReservation);

  // Wykonanie dodatkowych operacji zgodnie z innym kodem
  const secondDeliveryCheckbox = document.getElementById("secondDefaultCheck1");
  const modalCartFooter = document.getElementById("modal-cart-footer");
  if (secondDeliveryCheckbox.checked) {
    addDeliveryRow(modalCartFooter);
  }

  const totalPriceWithDelivery =
    calculateTotalPriceWithDelivery(cartTotalPrice);
  const modalCartTotalPrice = document.querySelector("#modal-cart-total-price");
  modalCartTotalPrice.innerText = totalPriceWithDelivery;
}

function addDeliveryRow(modalCartFooter) {
  const deliveryRow = document.createElement("tr");
  deliveryRow.innerHTML = `
    <td>Dostawa</td>
    <td>19 zł</td>
  `;
  modalCartFooter.appendChild(deliveryRow);
}

function calculateTotalPriceWithDelivery(cartTotalPrice) {
  const totalPrice = parseFloat(cartTotalPrice.replace("PLN ", ""));
  return "PLN " + (totalPrice + 19).toFixed(2);
}

// async function sendFormData(formData) {
//   try {
//     const response = await fetch("sendmail.php", {
//       method: "POST",
//       body: formData,
//     });

//     if (!response.ok) {
//       throw `Ошибка при отправке данных на сервер: ${response.status}`;
//     }

//     const json = await response.json();
//     if (json.result === "success") {
//       alert("Данные успешно отправлены на сервер!");
//       window.location.href = "http://testingstronks.pl";
//     } else {
//       alert("Ошибка на сервере: " + json.message);
//     }
//   } catch (error) {
//     alert("Произошла ошибка при отправке данных: " + error);
//   }
// }

function addToCartClicked(event) {
  const button = event.target;
  const shopItem = button.closest(".card");
  const title = shopItem.querySelector(".shop-item-title").innerText;
  const priceText = shopItem.querySelector(".shop-item-price").innerText;
  const price = parseFloat(priceText.replace(/[^\d.,]/g, "").replace(",", "."));
  const imageSrc = shopItem.querySelector(".shop-item-image").src;

  if (!isNaN(price)) {
    addItemToCart(title, price, imageSrc);
    updateCartTotal();
  } else {
    alert("Błąd: Nieprawidłowa cena produktu.");
  }
}

function addItemToCart(title, price, imageSrc) {
  const cartItems = document.querySelector(".cart-items");
  const cartItemNames = cartItems.querySelectorAll(".cart-item-title");

  for (let i = 0; i < cartItemNames.length; i++) {
    if (cartItemNames[i].innerText === title) {
      alert("Ten produkt został już dodany do koszyka!");
      return;
    }
  }

  const cartRow = document.createElement("tr");
  cartRow.classList.add("cart-row");

  const cartRowContents = `
      <td class="cart-item cart-column">
          <img class="cart-item-image" src="${imageSrc}" width="50" height="50">
          <br/>
          <span class="cart-item-title">${title}</span>
      </td>
      <td class="cart-price cart-column">${price.toFixed(2)} zł</td>
      <td class="cart-quantity cart-column">
          <input class="cart-quantity-input" type="number" value="1">
      </td>
      <td class="cart-delete cart-column">
          <button class="btn btn-danger btn-delete" type="button">Usuń</button>
      </td>
  `;
  cartRow.innerHTML = cartRowContents;
  cartItems.appendChild(cartRow);

  cartRow
    .querySelector(".cart-quantity-input")
    .addEventListener("change", quantityChanged);
  cartRow
    .querySelector(".btn-delete")
    .addEventListener("click", removeCartItem);
}

function removeCartItem(event) {
  const buttonClicked = event.target;
  buttonClicked.closest(".cart-row").remove();
  updateCartTotal();
}

function quantityChanged(event) {
  const input = event.target;
  if (isNaN(input.value) || input.value <= 0) {
    input.value = 1;
  }
  updateCartTotal();
}

function updateCartTotal() {
  const cartRows = document.querySelectorAll(".cart-row");
  let total = 0;

  cartRows.forEach((cartRow) => {
    const priceElement = cartRow.querySelector(".cart-price");
    const quantityElement = cartRow.querySelector(".cart-quantity-input");
    let price = parseFloat(priceElement.textContent.replace(" zł", ""));
    let quantity = parseInt(quantityElement.value);
    total += price * quantity;
  });

  if (document.getElementById("deliveryCheckbox").checked) {
    total += 19;
  }

  total = Math.round(total * 100) / 100;
  document.querySelector(".cart-total-price").innerText =
    "PLN " + total.toFixed(2);
}

document
  .getElementById("deliveryCheckbox")
  .addEventListener("change", function () {
    const totalPriceElement = document.querySelector(".cart-total-price");
    let totalPrice = parseFloat(
      totalPriceElement.textContent.replace("PLN ", "")
    );

    if (this.checked) {
      totalPrice += 19;
    } else {
      totalPrice -= 19;
    }

    totalPriceElement.textContent = "PLN " + totalPrice.toFixed(2);
  });

function addDeliveryRow() {
  const modalCartFooter = document.getElementById("modal-cart-footer");
  const deliveryRow = document.createElement("tr");
  deliveryRow.innerHTML = `
    <td>Dostawa</td>
    <td>19 zł</td>
  `;
  modalCartFooter.appendChild(deliveryRow);
}
