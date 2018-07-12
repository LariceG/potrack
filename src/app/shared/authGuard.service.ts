import { Injectable } from '@angular/core';
import { CanActivate, CanActivateChild } from '@angular/router';
import { Router, ActivatedRoute, Params } from '@angular/router';


@Injectable()
export class AuthGuard implements CanActivate, CanActivateChild {

  canActivate()
    {
        if (localStorage.getItem("AppToken") === null)
        {
          this.router.navigate(['/login']);
        }
        else
        {
          return true;
        }
    }

  constructor( private router: Router ) { }

  canActivateChild() {
    console.log('checking child route access');
    return true;
  }

}
