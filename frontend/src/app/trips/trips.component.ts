import { Component, OnInit } from '@angular/core';
import { FormBuilder, Validators } from '@angular/forms';
import { ApiService, Trip } from '../shared/api.service';

@Component({
  selector: 'app-trips',
  templateUrl: './trips.component.html',
  styleUrls: ['./trips.component.css']
})
export class TripsComponent implements OnInit {
  trips: Trip[] = [];
  weather: any = null;
  editingId: number | null = null;
  form = this.fb.group({
    destination: ['', Validators.required],
    start_date: ['', Validators.required],
    end_date: ['', Validators.required],
    budget: [0, [Validators.required, Validators.min(0)]]
  });

  constructor(private fb: FormBuilder, private api: ApiService) {}

  ngOnInit() {
    this.load();
  }

  load() {
    this.api.get<any>('/trips').subscribe({
      next: res => this.trips = res.trips || [],
      error: () => this.trips = []
    });
  }

  submit() {
    if (this.form.invalid) return;
    const request = this.editingId
      ? this.api.put<any>(`/trips/${this.editingId}`, this.form.value)
      : this.api.post<any>('/trips', this.form.value);

    request.subscribe(() => {
      this.form.reset({ destination: '', start_date: '', end_date: '', budget: 0 });
      this.editingId = null;
      this.load();
    });
  }

  edit(trip: Trip) {
    this.editingId = trip.id;
    this.form.patchValue({
      destination: trip.destination,
      start_date: trip.start_date,
      end_date: trip.end_date,
      budget: Number(trip.budget)
    });
  }

  remove(id: number) {
    if (!confirm('Excluir viagem?')) return;
    this.api.delete<any>(`/trips/${id}`).subscribe(() => this.load());
  }

  loadWeather(destination: string) {
    this.api.get<any>(`/weather?location=${encodeURIComponent(destination)}`).subscribe({
      next: res => this.weather = { destination, ...res.weather },
      error: () => this.weather = null
    });
  }
}
