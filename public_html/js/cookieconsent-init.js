// obtain plugin
var cc = initCookieConsent();

// run plugin with your configuration
cc.run({
  current_lang: 'en',
  autoclear_cookies: true,
  theme_css: '/stylev3/cookieconsent-2.7.2.min.css',
  force_consent: true,
  hide_from_bots: true,
  use_rfc_cookie: true,
  cookie_expiration: 1460,
  cookie_path: '/',
  cookie_same_site: 'Strict',

  languages: {
    'en': {
      consent_modal: {
        title: 'Much cookies, nom nom!',
        description: 'Hi, this website uses essential cookies to ensure its proper operation. No 3rd party cookies or tracking cookies are used.',
        primary_btn: {
          text: 'Accept necessary',
          role: 'accept_necessary'
        }
      },
      settings_modal: {
        title: 'Cookie preferences',
        save_settings_btn: 'Save settings',
        accept_all_btn: 'Accept all',
        reject_all_btn: 'Reject all',
        close_btn_label: 'Close',
        cookie_table_headers: [
        ],
        blocks: [
        ]
      }
    }
  },
  gui_options: {
    consent_modal: {
      layout: 'cloud',
      position: 'middle center'     // bottom/middle/top + left/right/center
    }
  }
});
