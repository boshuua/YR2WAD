import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient) { }

  loginUser(credentials: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/user_login.php`, credentials);
  }

  // We will build the component for this later
  adminCreateUser(userData: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/admin_create_user.php`, userData);
  }
  getAllUsers(): Observable<any> {
    return this.http.get(`${this.apiUrl}/get_users.php`);
  }
}