import { Component, OnInit } from '@angular/core';
import { ApiService, Trip } from '../shared/api.service';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css']
})
export class DashboardComponent implements OnInit {
  stats = { trips: 0, budget: 0, expenses: 0, reservations: 0 };
  trips: Trip[] = [];

  constructor(private api: ApiService) {}

  ngOnInit() {
    this.api.get<any>('/dashboard').subscribe({
      next: res => {
        this.stats = res.stats;
        this.trips = res.trips || [];
      },
      error: () => this.stats = { trips: 0, budget: 0, expenses: 0, reservations: 0 }
    });
  }
}
