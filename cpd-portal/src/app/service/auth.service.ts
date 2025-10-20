import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = 'http://localhost:8000/api'; // Make sure this URL is correct

  constructor(private http: HttpClient) { }

  loginUser(credentials: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/user_login.php`, credentials, { withCredentials: true });
  }

  adminCreateUser(userData: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/admin_create_user.php`, userData, { withCredentials: true });
  }

  // Renamed from getAllUsers - Fetches all users
  getUsers(): Observable<any> {
    // Calls the script without an ID parameter
    return this.http.get(`${this.apiUrl}/get_users.php`, { withCredentials: true });
  }

  // Fetches a single user by ID
  getUserById(userId: number): Observable<any> {
    // Calls the *same* script but adds the ID query parameter
    return this.http.get(`${this.apiUrl}/get_users.php?id=${userId}`, { withCredentials: true });
  }

  adminUpdateUser(userId: number, userData: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/admin_update_user.php?id=${userId}`, userData, {
      withCredentials: true
    });
  }

  adminDeleteUser(userId: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/admin_delete_user.php?id=${userId}`, {
      withCredentials: true
    });
  }
  getActivityLog(limit: number = 20): Observable<any> { // Default limit
    return this.http.get(`${this.apiUrl}/get_activity_log.php?limit=${limit}`, { withCredentials: true });
  }
}