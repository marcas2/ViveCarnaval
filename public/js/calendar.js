 document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const modal = document.getElementById('eventModal');
    const modalContainer = modal.querySelector('.modal-container');
    const closeModal = document.getElementById('closeModal');

    const modalTitle = document.getElementById('modalTitle');
    const modalFecha = document.getElementById('modalFecha');
    const modalTipo = document.getElementById('modalTipo');
    const modalDescripcion = document.getElementById('modalDescripcion');
    const modalImagen = document.getElementById('modalImagen');
    const modalImageContainer = document.getElementById('modalImageContainer');

    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      locale: 'es',
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
      },
      events: '/api/events',
      eventContent: function(arg) {
        const eventType = arg.event.extendedProps.tipo || 'General';
        const eventEmoji = getEventEmoji(eventType);
        
        return {
          html: `
            <div class="fc-event-content flex items-center gap-2">
              <span class="text-xs">${eventEmoji}</span>
              <span class="fc-event-title flex-1 truncate">${arg.event.title}</span>
            </div>
          `
        };
      },
      eventClick: function(info) {
        const e = info.event;
        const ex = e.extendedProps;

        console.log("✅ Evento clickeado:", e);

        modalTitle.textContent = e.title || "Sin título";
        modalFecha.textContent = e.start.toLocaleDateString('es-CO', { 
          weekday: 'long',
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });
        modalTipo.textContent = ex.tipo || "General";
        modalDescripcion.textContent = ex.description || "Sin descripción disponible";

        if (ex.imagen) {
          modalImagen.src = ex.imagen.startsWith('http') ? ex.imagen : window.location.origin + ex.imagen;
          modalImageContainer.classList.remove("hidden");
        } else {
          modalImageContainer.classList.add("hidden");
        }

        // 👉 Mostrar modal con animación
        modal.classList.remove("opacity-0", "pointer-events-none");
        modal.classList.add("opacity-100");
        setTimeout(() => {
          modalContainer.classList.add("modal-open");
        }, 50);
        document.body.style.overflow = "hidden";
      }
    });

    calendar.render();

    function getEventEmoji(tipo) {
      const emojiMap = {
        'Conferencia': '🎤',
        'Taller': '🔧',
        'Social': '🎉',
        'Deportivo': '⚽',
        'Cultural': '🎭',
        'Educativo': '📚',
        'General': '📅'
      };
      return emojiMap[tipo] || '📅';
    }

    function closeModalFn() {
      modalContainer.classList.remove("modal-open");
      setTimeout(() => {
        modal.classList.add("opacity-0", "pointer-events-none");
        modal.classList.remove("opacity-100");
        document.body.style.overflow = "";
      }, 300);
    }

    closeModal.addEventListener("click", closeModalFn);
    modal.addEventListener("click", (e) => {
      if (e.target === modal) closeModalFn();
    });
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") closeModalFn();
    });
  });