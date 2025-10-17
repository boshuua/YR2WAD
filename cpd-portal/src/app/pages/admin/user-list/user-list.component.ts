import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AuthService } from '../../../service/auth.service';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-user-list',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './user-list.component.html',
  styleUrls: ['./user-list.component.css']
})
export class UserListComponent implements OnInit {
  users: any[] = []; // Array to hold the list of users

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
  
  // We will add the deleteUser() method here || DONE
  deleteUser(userId: number, userName: string): void {
    console.log(`Attempting to delete user with ID: ${userId}`);
    // Simple confirmation dialog
    if (confirm(`Are you sure you want to delete the user "${userName}"?`)) {
      this.authService.adminDeleteUser(userId).subscribe({
        next: (response) => {
          alert(response.message || 'User deleted successfully!');
          // Remove the user from the local array to update the list instantly
          this.users = this.users.filter(user => user.id !== userId);
        },
        error: (err) => {
          console.error('Failed to delete user', err);
          alert('Error deleting user: ' + (err.error?.message || err.message)); // Show specific error from backend if available
        }
      });
    }
  }
}