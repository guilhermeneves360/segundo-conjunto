import { Component } from '@angular/core';
import { FormBuilder, Validators } from '@angular/forms';
import { AuthService } from '../../shared/auth.service';
import { ApiService } from '../../shared/api.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent {
  resetToken = '';
  form = this.fb.group({
    email: ['', [Validators.required, Validators.email]],
    password: ['', [Validators.required]]
  });
  forgotForm = this.fb.group({
    email: ['', [Validators.required, Validators.email]]
  });
  resetForm = this.fb.group({
    token: ['', Validators.required],
    password: ['', [Validators.required, Validators.minLength(6)]]
  });

  constructor(private fb: FormBuilder, private api: ApiService, private auth: AuthService) {}

  submit() {
    if (this.form.invalid) return;
    this.api.post<any>('/auth/login', this.form.value).subscribe(
      res => {
        this.auth.setToken(res.token);
        this.auth.setUser(res.user);
        window.location.href = '/';
      },
      err => alert(err.error.message || 'Erro')
    );
  }

  forgotPassword() {
    if (this.forgotForm.invalid) return;
    this.api.post<any>('/auth/forgot-password', this.forgotForm.value).subscribe(res => {
      this.resetToken = res.reset_token;
      this.resetForm.patchValue({ token: res.reset_token });
      alert(`${res.message}: ${res.reset_token}`);
    });
  }

  resetPassword() {
    if (this.resetForm.invalid) return;
    this.api.post<any>('/auth/reset-password', this.resetForm.value).subscribe(res => alert(res.message));
  }
}
