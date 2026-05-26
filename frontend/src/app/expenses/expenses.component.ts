import { Component, OnInit } from '@angular/core';
import { FormBuilder, Validators } from '@angular/forms';
import { ApiService, Trip } from '../shared/api.service';

@Component({
  selector: 'app-expenses',
  templateUrl: './expenses.component.html',
  styleUrls: ['./expenses.component.css']
})
export class ExpensesComponent implements OnInit {
  trips: Trip[] = [];
  selectedTripId: number | null = null;
  expenses: any[] = [];
  itinerary: any[] = [];
  reservations: any[] = [];
  total = 0;

  expenseForm = this.fb.group({
    trip_id: [null as number | null, Validators.required],
    category: ['', Validators.required],
    amount: [0, [Validators.required, Validators.min(0.01)]],
    description: ['', Validators.required]
  });

  itineraryForm = this.fb.group({
    trip_id: [null as number | null, Validators.required],
    activity_date: ['', Validators.required],
    activity_time: ['', Validators.required],
    description: ['', Validators.required]
  });

  reservationForm = this.fb.group({
    trip_id: [null as number | null, Validators.required],
    type: ['Hotel', Validators.required],
    details: ['', Validators.required]
  });

  constructor(private fb: FormBuilder, private api: ApiService) {}

  ngOnInit() {
    this.api.get<any>('/trips').subscribe(res => {
      this.trips = res.trips || [];
      if (this.trips.length) this.selectTrip(this.trips[0].id);
    });
  }

  selectTrip(id: number) {
    this.selectedTripId = Number(id);
    this.expenseForm.patchValue({ trip_id: this.selectedTripId });
    this.itineraryForm.patchValue({ trip_id: this.selectedTripId });
    this.reservationForm.patchValue({ trip_id: this.selectedTripId });
    this.loadDetails();
  }

  loadDetails() {
    if (!this.selectedTripId) return;
    this.api.get<any>(`/expenses/${this.selectedTripId}`).subscribe(res => {
      this.expenses = res.expenses || [];
      this.total = Number(res.total || 0);
    });
    this.api.get<any>(`/itinerary/${this.selectedTripId}`).subscribe(res => this.itinerary = res.itinerary || []);
    this.api.get<any>(`/reservations/${this.selectedTripId}`).subscribe(res => this.reservations = res.reservations || []);
  }

  addExpense() {
    if (this.expenseForm.invalid) return;
    this.api.post<any>('/expenses', this.expenseForm.value).subscribe(() => {
      this.expenseForm.patchValue({ category: '', amount: 0, description: '' });
      this.loadDetails();
    });
  }

  addItinerary() {
    if (this.itineraryForm.invalid) return;
    this.api.post<any>('/itinerary', this.itineraryForm.value).subscribe(() => {
      this.itineraryForm.patchValue({ activity_date: '', activity_time: '', description: '' });
      this.loadDetails();
    });
  }

  addReservation() {
    if (this.reservationForm.invalid) return;
    this.api.post<any>('/reservations', this.reservationForm.value).subscribe(() => {
      this.reservationForm.patchValue({ type: 'Hotel', details: '' });
      this.loadDetails();
    });
  }

  exportCsv() {
    if (!this.selectedTripId) return;
    window.open(this.api.fileUrl(`/exports/expenses-csv/${this.selectedTripId}`), '_blank');
  }
}
