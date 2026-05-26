import { Component, HostBinding } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { AuthService } from './shared/auth.service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  @HostBinding('class.dark-mode') darkMode = false;
  @HostBinding('class.light-mode') get lightMode() {
    return !this.darkMode;
  }

  constructor(public translate: TranslateService, public auth: AuthService) {
    translate.addLangs(['pt', 'en']);
    const browserLang = translate.getBrowserLang();
    translate.setDefaultLang('pt');
    translate.use(localStorage.getItem('language') || browserLang || 'pt');
    this.darkMode = localStorage.getItem('theme') === 'dark';
  }

  toggleTheme() {
    this.darkMode = !this.darkMode;
    localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
  }

  changeLanguage(lang: string) {
    this.translate.use(lang);
    localStorage.setItem('language', lang);
  }
}
