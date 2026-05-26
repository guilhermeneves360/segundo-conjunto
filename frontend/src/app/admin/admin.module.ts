import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TranslateModule } from '@ngx-translate/core';
import { AdminComponent } from './admin.component';

@NgModule({
  declarations: [AdminComponent],
  imports: [CommonModule, TranslateModule],
  exports: [AdminComponent]
})
export class AdminModule {}
