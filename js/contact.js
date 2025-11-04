// Client-side contact form handling: store to localStorage and notify admin via email
$(document).ready(function(){
  (function($) {
    "use strict";

    function getVal(id){ return $(id).val().trim(); }

    $('#contactForm').on('submit', async function(e){
      e.preventDefault();

      const message = getVal('#message');
      const name = getVal('#name');
      const email = getVal('#email');
      const subject = getVal('#subject');

      // Basic validation
      if (!name || !email || !subject || !message) {
        alert('Please fill in Name, Email, Subject and Message.');
        return;
      }

      const entry = {
        name, email, subject, message,
        submittedDate: new Date().toISOString(),
        status: 'new'
      };

      try {
        const key = 'charity_contacts';
        const arr = JSON.parse(localStorage.getItem(key) || '[]');
        arr.push(entry);
        localStorage.setItem(key, JSON.stringify(arr));

        // Email admin
        const body = [
          {label:'New contact submission'},
          {label:'Name', value:name},
          {label:'Email', value:email},
          {label:'Subject', value:subject},
          {label:'Message', value:message},
          {label:'Submitted', value:new Date(entry.submittedDate).toLocaleString()}
        ].map(l => (l.value!==undefined? `${l.label}: ${l.value}`: l.label)).join('\n');

        if (window.notifications) {
          await notifications.sendAdminEmail(`📥 Contact: ${subject}`, body);
        }

        alert('✅ Thank you! Your message has been sent.');
        this.reset();
      } catch (err) {
        console.error(err);
        alert('❌ Failed to submit. Please try again later.');
      }
    });
  })(jQuery);
});