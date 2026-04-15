(function () {
  function bindFancybox() {
    if (!window.Fancybox || typeof window.Fancybox.show !== 'function') {
      return;
    }

    document.querySelectorAll('[data-be-gallery-trigger]').forEach(function (button) {
      button.addEventListener('click', function () {
        var galleryId = button.getAttribute('data-be-gallery-trigger');
        var dataNode = document.querySelector('.be-single-property__gallery-data[data-be-gallery-id="' + galleryId + '"]');

        if (!dataNode) {
          return;
        }

        var items = [];

        try {
          items = JSON.parse(dataNode.textContent || '[]');
        } catch (error) {
          items = [];
        }

        if (!Array.isArray(items) || items.length === 0) {
          return;
        }

        window.Fancybox.show(items, {
          animated: true,
          dragToClose: true,
          Images: {
            zoom: true,
          },
          Toolbar: {
            display: {
              left: ['infobar'],
              middle: [],
              right: ['close'],
            },
          },
          Thumbs: {
            type: 'classic',
          },
        });
      });
    });
  }

  function toggleBodyLock(shouldLock) {
    document.documentElement.classList.toggle('be-single-property-modal-open', shouldLock);
    document.body.classList.toggle('be-single-property-modal-open', shouldLock);
  }

  function closeModal(modal) {
    if (!modal) {
      return;
    }

    modal.hidden = true;
    modal.setAttribute('aria-hidden', 'true');
    toggleBodyLock(false);
  }

  function openModal(modal) {
    if (!modal) {
      return;
    }

    modal.hidden = false;
    modal.setAttribute('aria-hidden', 'false');
    toggleBodyLock(true);
  }

  document.addEventListener('DOMContentLoaded', function () {
    bindFancybox();

    const modals = Array.from(document.querySelectorAll('[data-be-modal]'));

    modals.forEach((modal) => {
      modal.querySelectorAll('[data-be-modal-close]').forEach((button) => {
        button.addEventListener('click', function () {
          closeModal(modal);
        });
      });
    });

    document.querySelectorAll('[data-be-modal-open]').forEach((button) => {
      button.addEventListener('click', function () {
        const target = button.getAttribute('data-be-modal-open');
        openModal(document.querySelector('[data-be-modal="' + target + '"]'));
      });
    });

    document.addEventListener('keydown', function (event) {
      if (event.key !== 'Escape') {
        return;
      }

      const activeModal = document.querySelector('[data-be-modal]:not([hidden])');
      if (activeModal) {
        closeModal(activeModal);
      }
    });
  });
})();
