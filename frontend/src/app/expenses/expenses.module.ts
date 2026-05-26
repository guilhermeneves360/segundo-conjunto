import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { TranslateModule } from '@ngx-translate/core';
import { ExpensesComponent } from './expenses.component';

@NgModule({
  declarations: [ExpensesComponent],
  imports: [CommonModule, ReactiveFormsModule, TranslateModule],
  exports: [ExpensesComponent]
})
export class ExpensesModule {}
