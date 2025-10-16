import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AuthService } from '../../service/auth.service'; // Correct path to service
import { AddUserFormComponent } from '../../components/add-user-form/add-user-form.component';
@Component({
  selector: 'app-admin-dashboard',
  standalone: true,
  imports: [CommonModule, AddUserFormComponent],
  templateUrl: './admin-dashboard.component.html',
  styleUrls: ['./admin-dashboard.component.css']
})
export class AdminDashboardComponent implements OnInit {
  users: any[] = []; // Array to hold the list of users
  showAddUserForm = false;
  constructor(private authService: AuthService) {}

  ngOnInit(): void {
    this.loadUsers();
  }

  loadUsers(): void {
    this.authService.getAllUsers().subscribe({
      next: (data) => {
        this.users = data;
      },
      error: (err) => {
        console.error('Failed to load users', err);
      }
    });
  }

  onUserAdded(): void {
    this.loadUsers(); // Refresh the user list
  }
}