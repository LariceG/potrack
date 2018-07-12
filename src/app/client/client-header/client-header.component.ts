import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';

@Component({
  selector: 'app-client-header',
  templateUrl: './client-header.component.html',
  styleUrls: ['./client-header.component.css']
})

export class ClientHeaderComponent implements OnInit {
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
