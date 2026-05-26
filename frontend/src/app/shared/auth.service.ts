import { Injectable } from '@angular/core';
import { Router } from '@angular/router';

@Injectable({ providedIn: 'root' })
export class AuthService {
  constructor(private router: Router) {}

  logout() {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('auth_user');
    this.router.navigate(['/login']);
  }

  setToken(token: string) {
    localStorage.setItem('auth_token', token);
  }

  setUser(user: any) {
    localStorage.setItem('auth_user', JSON.stringify(user));
  }

  getUser(): any {
    const raw = localStorage.getItem('auth_user');
    return raw ? JSON.parse(raw) : null;
  }

  isAdmin(): boolean {
    return this.getUser()?.type === 'admin';
  }

  getToken(): string | null {
    return localStorage.getItem('auth_token');
  }

  isAuthenticated(): boolean {
    return !!this.getToken();
  }
}
