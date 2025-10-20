import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AuthService } from '../../../service/auth.service';
import { RouterLink } from '@angular/router';
import { ConfirmModalComponent } from '../../../components/confirm-modal/confirm-modal.component';

@Component({
  selector: 'app-user-list',
  standalone: true,
  // Add ConfirmModalComponent to imports
  imports: [CommonModule, RouterLink, ConfirmModalComponent],
  templateUrl: './user-list.component.html',
  styleUrls: ['./user-list.component.css']
})
export class UserListComponent implements OnInit {
  users: any[] = [];
  showDeleteConfirmModal = false;
  userToDeleteId: number | null = null;
  userToDeleteName: string = '';

  constructor(private authService: AuthService) { }

  ngOnInit(): void {
    this.loadUsers();
  }

  loadUsers(): void {
    // *** Corrected: Call getUsers ***
    this.authService.getUsers().subscribe({ // <--- Changed method name
      next: (data) => {
        this.users = data;
      },
      error: (err) => {
        console.error('Failed to load users', err);
        alert('Error loading users: ' + (err.error?.message || err.message));
      }
    });
  }
  promptDeleteUser(userId: number, userName: string): void {
    console.log('Prompting delete for user ID:', userId);
    this.userToDeleteId = userId;
    this.userToDeleteName = userName;
    // Try this:
    setTimeout(() => {
      this.showDeleteConfirmModal = true; // Show the modal
    }, 0);
  }
  confirmDelete(): void {
    if (this.userToDeleteId !== null) {
      this.authService.adminDeleteUser(this.userToDeleteId).subscribe({
        next: (response) => {
          alert(response.message || 'User deleted successfully!');
          this.users = this.users.filter(user => user.id !== this.userToDeleteId);
          this.closeDeleteModal(); // Close modal on success
        },
        error: (err) => {
          console.error('Failed to delete user', err);
          alert('Error deleting user: ' + (err.error?.message || err.message));
          this.closeDeleteModal(); // Close modal on error too
        }
      });
    } else {
      console.error("User ID to delete is null");
      this.closeDeleteModal();
    }
  }

  cancelDelete(): void {
    this.closeDeleteModal();
  }

  /** Helper to reset modal state */
  private closeDeleteModal(): void {
    this.showDeleteConfirmModal = false;
    this.userToDeleteId = null;
    this.userToDeleteName = '';
  }
}