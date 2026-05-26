import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { DashboardComponent } from './dashboard.component';
import { TranslateModule } from '@ngx-translate/core';

@NgModule({
  declarations: [DashboardComponent],
  imports: [CommonModule, TranslateModule],
  exports: [DashboardComponent]
})
export class DashboardModule {}
