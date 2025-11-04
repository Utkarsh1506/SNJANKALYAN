/* Notifications and Email utilities for SNJANKALYAN (client-side)
 * - Uses mailto: as a fallback to deliver emails to the configured admin address
 * - Optionally supports EmailJS (configure below)
 */

const notifications = (function() {
  function getStoredEmailJsConfig() {
    try {
      const raw = localStorage.getItem('charity_emailjs');
      if (!raw) return null;
      return JSON.parse(raw);
    } catch {
      return null;
    }
  }

  // Default (disabled) config until admin sets up EmailJS in Settings
  const DEFAULT_EMAILJS = { enabled: false, serviceId: '', templateId: '', publicKey: '' };

  function getAdminEmail() {
    try {
      const adminStr = localStorage.getItem('charity_admin');
      if (!adminStr) return null;
      const admin = JSON.parse(adminStr);
      return admin.email || null;
    } catch {
      return null;
    }
  }

  function encodeBody(body) {
    return encodeURIComponent(body).replace(/%0A/g, '\n');
  }

  function formatPairs(pairs) {
    return pairs
      .filter(p => p && p.label)
      .map(p => `${p.label}: ${p.value ?? ''}`)
      .join('\n');
  }

  async function sendAdminEmail(subject, bodyText) {
    const to = getAdminEmail();
    if (!to) {
      console.warn('Admin email not configured. Email skipped.');
      return { success: false, error: 'Admin email not configured' };
    }

    // If EmailJS configured, try that first
    const cfg = Object.assign({}, DEFAULT_EMAILJS, getStoredEmailJsConfig() || {});
    if (cfg.enabled && window.emailjs) {
      try {
        // optional init (safe to call multiple times)
        if (cfg.publicKey && emailjs.init) {
          try { emailjs.init(cfg.publicKey); } catch(e) {}
        }
        await emailjs.send(cfg.serviceId, cfg.templateId, {
          to_email: to,
          subject: subject,
          message: bodyText
        }, cfg.publicKey);
        return { success: true, method: 'emailjs' };
      } catch (e) {
        console.error('EmailJS send failed; falling back to mailto:', e);
      }
    }

    // Fallback: open mailto link
    const link = document.createElement('a');
    link.href = `mailto:${encodeURIComponent(to)}?subject=${encodeURIComponent(subject)}&body=${encodeBody(bodyText)}`;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    setTimeout(() => document.body.removeChild(link), 0);
    return { success: true, method: 'mailto' };
  }

  return {
    getAdminEmail,
    sendAdminEmail,
    formatPairs
  };
})();
