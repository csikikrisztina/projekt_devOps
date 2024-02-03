function requestNewArrivals() {
    fetchCall("newArrivals.php", responseNewArrivals);
    function responseNewArrivals(data) {
      const newArrivals = data.newArrivals;
      const newArrivalsSection = document.querySelector(".new-arrivals");
      populateCatalogue(newArrivals, newArrivalsSection);
    }
  }