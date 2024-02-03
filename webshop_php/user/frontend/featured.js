function requestFeatured() {
    fetchCall("featured.php", responseFeatured);
    function responseFeatured(data) {
      const featured = data.featured;
      const featuredSection = document.querySelector(".featured-products");
      populateCatalogue(featured, featuredSection);
    }
  }
 