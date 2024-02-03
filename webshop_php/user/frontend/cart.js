const cartIcon = document.querySelector(".cart");
const localCart = {
  cart: null,
  length: 0,
  total: 0.0,
};
cartIcon.addEventListener("click", showCart);
function updateCart() {
  fetchCall("cart.php", responseUpdateCart);
}

function responseUpdateCart(data) {
  console.log(data);
  const { total, ...cart } = data.cart;
  localCart.cart = cart;
  localCart.total = total;
  localCart.length = Object.keys(cart).length;
  console.log(localCart.length);
  if (localCart.length > 0) {
    cartIcon.classList.add("cart-not-empty");
    const rootCss = document.querySelector(":root");
    rootCss.style.setProperty("--cart-size", `'${localCart.length}'`);
  } else {
    cartIcon.classList.remove("cart-not-empty");
  }
}

function addProductToCart() {
  console.log(this);
  const select = document.querySelector("select");
  console.log(select.value);
  const payload = new URLSearchParams();
  payload.append("id", this.id);
  payload.append("image", this.image);
  payload.append("price", this.price);
  payload.append("stock", this.stock);
  payload.append("quantity", select.value);
  fetchCall("cart.php", responseUpdateCart, "POST", payload);
}

function showCart() {
  const main = document.querySelector("main");
  if (localCart.length <= 0) {
    if (main.children[0].classList.contains("cart-container"))
      location.replace(location.pathname);
    else alert("Cart is empty");
    return;
  }
  setActiveCategory(null);

  main.innerHTML = "";
  const container = document.createElement("div");
  container.className = "cart-container";
  const imgHeading = document.createElement("div");
  imgHeading.textContent = "Item";
  container.appendChild(imgHeading);

  const quantityHeading = document.createElement("div");
  quantityHeading.textContent = "Quantity";
  container.appendChild(quantityHeading);

  const availabilityHeading = document.createElement("div");
  availabilityHeading.textContent = "Availability";
  container.appendChild(availabilityHeading);

  const orderHeading = document.createElement("div");
  orderHeading.textContent = "Order Value";
  container.appendChild(orderHeading);
  for (const [id, product] of Object.entries(localCart.cart)) {
    const { image, price, quantity, stock } = product;
    const imgDiv = document.createElement("div");
    const imgElm = document.createElement("img");
    imgElm.src = `http://localhost:8081${image}`;
    imgDiv.appendChild(imgElm);
    container.appendChild(imgDiv);
    const quantDiv = document.createElement("div");
    const select = document.createElement("select");
    select.addEventListener("change", updateQuantity.bind(id));
    const counter = stock > 10 ? 10 : stock;
    for (let i = 1; i <= counter; i++) {
      const option = document.createElement("option");
      option.value = i;
      option.textContent = i;
      if (i === +quantity) {
        option.setAttribute("selected", "");
      }
      select.appendChild(option);
    }
    quantDiv.appendChild(select);
    container.appendChild(quantDiv);

    const stockDiv = document.createElement("div");
    switch (true) {
      case stock > 10:
        stockDiv.textContent = "in stock";
        break;
      case stock > 0 && stock <= 10:
        stockDiv.textContent = `only ${stock} left`;
        break;
      case stock == 0:
        stockDiv.textContent = `out of stock`;
        break;
      default:
        stockDiv.textContent = `in stock`;
        break;
    }
    container.appendChild(stockDiv);

    const priceDiv = document.createElement("div");
    priceDiv.textContent = `${price} RON`;
    const deleteBtn = document.createElement("button");
    deleteBtn.className = "delete-product-btn";
    deleteBtn.addEventListener("click", deleteProduct.bind(id));
    deleteBtn.textContent = "Delete";
    priceDiv.appendChild(deleteBtn);
    container.appendChild(priceDiv);
  }
  const totalDiv = document.createElement("div");
  totalDiv.className = "total-div";
  totalDiv.textContent = `Total ${localCart.total}`;
  container.appendChild(totalDiv);

  const navDiv = document.createElement("div");
  navDiv.className = "nav-div";
  const continueShoppingBtn = document.createElement("button");
  continueShoppingBtn.className = "continue-shopping-btn";
  continueShoppingBtn.textContent = "Continue Shopping";
  navDiv.appendChild(continueShoppingBtn);

  const checkoutBtn = document.createElement("button");
  checkoutBtn.className = "checkout-btn";
  checkoutBtn.textContent = "Checkout";
  checkoutBtn.addEventListener('click', checkout);
  navDiv.appendChild(checkoutBtn);
  container.appendChild(navDiv);

  main.appendChild(container);
}
function updateQuantity(e) {
  const payload = new URLSearchParams();
  payload.append("quantity", e.target.value);
  payload.append("id", this);
  fetchCall("cart.php", responseUpdateQuantity, "PATCH", payload);
}
function responseUpdateQuantity(data) {
  responseUpdateCart(data);
  showCart();
}
function deleteProduct() {
  const payload = new URLSearchParams();
  payload.append("id", this);
  fetchCall("cart.php", responseUpdateQuantity, "DELETE", payload);
}
