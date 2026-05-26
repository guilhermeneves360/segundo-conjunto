import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { TranslateModule } from '@ngx-translate/core';
import { TripsComponent } from './trips.component';

@NgModule({
  declarations: [TripsComponent],
  imports: [CommonModule, ReactiveFormsModule, TranslateModule],
  exports: [TripsComponent]
})
export class TripsModule {}
