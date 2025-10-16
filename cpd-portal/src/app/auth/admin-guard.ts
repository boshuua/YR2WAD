import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';

export const adminGuard: CanActivateFn = (route, state) => {
  const router = inject(Router);
  const userItem = sessionStorage.getItem('currentUser');
  if (userItem) {
    const user = JSON.parse(userItem);
    if (user.access_level === 'admin') {
      return true;
    }
  }
  router.navigate(['/login']);
  return false; // access would be DENIED
}