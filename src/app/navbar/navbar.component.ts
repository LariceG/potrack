import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';

@Component({
  selector: 'app-navbar',
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.css']
})

export class NavbarComponent implements OnInit {
token;

constructor( private router: Router)
{

}

  ngOnInit()
  {
    let tkn    = localStorage.getItem('AppToken');
    this.token  = JSON.parse(tkn);

  }

  destroytoken()
  {
    localStorage.removeItem('AppToken');
    this.router.navigate(['/login']);
  }

}
