<!-- includes/footer.php -->
  </main>
  <footer class="bg-dark text-white py-4">
    <div class="container">
      <h5>Contactez-nous :</h5>
      <ul class="list-unstyled">
        <li>Email : contact@sportify.com</li>
        <li>Téléphone : +33 1 23 45 67 89</li>
        <li>Adresse : 10 Rue du Sport, 75000 Paris</li>
      </ul>
    </div>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var triggers = document.querySelectorAll('#accountTabs button');
    triggers.forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var tab = new bootstrap.Tab(btn);
        tab.show();
      });
    });
  });
</script>
</body>
</html>
