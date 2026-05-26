import { Component } from '@angular/core';
import { FormBuilder, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { ApiService } from '../../shared/api.service';

@Component({
  selector: 'app-register',
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.css']
})
export class RegisterComponent {
  form = this.fb.group({
    name: ['', [Validators.required]],
    email: ['', [Validators.required, Validators.email]],
    password: ['', [Validators.required, Validators.minLength(6)]]
  });

  constructor(private fb: FormBuilder, private api: ApiService, private router: Router) {}

  submit() {
    if (this.form.invalid) return;
    this.api.post<any>('/auth/register', this.form.value).subscribe(
      () => this.router.navigate(['/login']),
      err => alert(err.error.message || 'Erro')
    );
  }
}
