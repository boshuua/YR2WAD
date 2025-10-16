import { Component, EventEmitter, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AuthService } from '../../service/auth.service';

@Component({
  selector: 'app-add-user-form',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './add-user-form.component.html',
  styleUrls: ['./add-user-form.component.css']
})
export class AddUserFormComponent {
  @Output() userAdded = new EventEmitter<void>();
  @Output() closeForm = new EventEmitter<void>();

  userData = {
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    job_title: '',
    access_level: 'user' // Default to 'user'
  };

  constructor(private authService: AuthService) {}

  onSubmit(): void {
    this.authService.adminCreateUser(this.userData).subscribe({
      next: () => {
        alert('User created successfully!');
        this.userAdded.emit(); // Notify the parent to refresh the user list
        this.close();
      },
      error: (err: any) => {
        console.error('Failed to create user', err);
        alert('Error: ' + err.error.message);
      }
    });
  }

  close(): void {
    this.closeForm.emit(); // Notify the parent to close the modal
  }
}