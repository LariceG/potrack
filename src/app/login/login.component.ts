import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { FormBuilder, FormGroup , Validators } from '@angular/forms';
import {HttpModule, Http,Response} from '@angular/http';
import { LoginService }    from './login.service';
import {ToasterModule, ToasterService} from 'angular2-toaster';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css'],
  providers: [LoginService]
})


export class LoginComponent implements OnInit {

  private toasterService: ToasterService;
  login : FormGroup;
  http: Http;
  loginsubmitted: boolean = false;
//
  constructor( toasterService: ToasterService , fb: FormBuilder , public _http: Http , private _service: LoginService , private router: Router)
  {
      this.toasterService = toasterService;
      this.http = _http;
      this.login = fb.group({
      'username' : [null,Validators.required],
      'password': [null,Validators.required]
    })
  }

  responseStatus:Object= [];
  submitForm(value: any)
  {
    this.loginsubmitted = true;

    if( !this.login.valid )
    {
      return false;
    }

    this.toasterService.pop('info',' Loading...', '' );
    this._service.login(value).subscribe(
      data => {
        if(data.success)
        {
            this.loginsubmitted = false;
            this.toasterService.clear();
            this.toasterService.pop('success', 'Login Successful,' +' Redirecting...', '' );
            var tkn = JSON.stringify(data.data);
            localStorage.setItem('AppToken', tkn);
            setTimeout((router: Router) => {
              if(data.data['userType'] == 4)
                this.router.navigate(['/client-dashboard']);
                else
                this.router.navigate(['/dashboard']);
            }, 1000);
        }
        else
        {
          this.toasterService.clear();
          this.toasterService.pop('error', data.data, '' );
        }
      },
      err => {
        // this.toasterService.clear();
        // if(err.status == 409)
        // this.toasterService.pop('error', 'Invalid Login Details', '' );
        // else
        // this.toasterService.pop('error', 'Something wro ng,try again', '' );
    }
   );
  }

  ngOnInit() {

  }

}
