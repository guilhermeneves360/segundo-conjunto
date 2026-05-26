import { Component, OnInit } from '@angular/core';
import { ApiService } from '../shared/api.service';

@Component({
  selector: 'app-admin',
  templateUrl: './admin.component.html',
  styleUrls: ['./admin.component.css']
})
export class AdminComponent implements OnInit {
  users: any[] = [];

  constructor(public api: ApiService) {}

  ngOnInit() {
    this.load();
  }

  load() {
    this.api.get<any>('/admin/users').subscribe({
      next: res => this.users = res.users || [],
      error: () => this.users = []
    });
  }

  changeType(user: any, type: string) {
    this.api.put<any>(`/admin/users/${user.id}`, { type }).subscribe(() => this.load());
  }

  remove(id: number) {
    if (!confirm('Excluir utilizador?')) return;
    this.api.delete<any>(`/admin/users/${id}`).subscribe(() => this.load());
  }

  exportTrips() {
    window.open(this.api.fileUrl('/exports/trips-pdf'), '_blank');
  }
}
