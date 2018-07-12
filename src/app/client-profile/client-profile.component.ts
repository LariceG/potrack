import { OnInit } from '@angular/core';
import { Component, Input, Output, EventEmitter } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { ViewChild, ElementRef, AfterViewInit } from '@angular/core';
import { FormArray , FormControl , FormBuilder, FormGroup , Validators ,AbstractControl , ValidationErrors} from '@angular/forms';
import { ToasterModule, ToasterService} from 'angular2-toaster';
declare var jQuery: any;
import * as myGlobals from '../shared/globals';
import { PoService }    from '../purchase-orders/po.service';

function phnMatcher( control: FormControl )
{
  var phn  = control.value;
  if(phn)
  {
   var reGoodDate = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/;
   return reGoodDate.test(phn) ? null : { 'nomatch' : true };
 }
}

function dateMatcher( control: FormControl )
{
  var date  = control.value;
  if(date)
  {
    var reGoodDate = /^((0?[1-9]|1[012])[- /.](0?[1-9]|[12][0-9]|3[01])[- /.](19|20)?[0-9]{2})*$/;
    return reGoodDate.test(date) ? null : { 'nomatch' : true };
  }
}



function emailOrEmpty(control: AbstractControl): ValidationErrors | null {
    return control.value === '' ? null : Validators.email(control);
}


@Component({
  selector: 'app-client-profile',
  templateUrl: './client-profile.component.html',
  styleUrls: ['./client-profile.component.css'],
  providers:[PoService]
})

export class ClientProfileComponent implements OnInit
{
  private toasterService: ToasterService;
  Client = {};
  poForm : FormGroup;
  poFormSubmitted;
  cl = '';
  clientPic = '';
  showImageUploading : boolean = false;
  @ViewChild('fileInput') fileInput: ElementRef;
  Url = '';

  constructor( private router: Router , toasterService: ToasterService , private fb: FormBuilder , private activatedRoute: ActivatedRoute , private po: PoService )
  {
    this.poForm = fb.group({
    'clientFirstName' : ['',Validators.required],
    'clientLastName': [,Validators.required],
    'clientCompany': [,Validators.required],
    'clientTelephone': [,[Validators.required,phnMatcher]],
    'clientEmail': [,[Validators.required,Validators.email]],
    'clientBillingAddress': [,Validators.required],
    'clientCity': [,Validators.required],
    'clientCountry': [,Validators.required],
    'clientPostal': [,Validators.required],
    'clientTag': [''],
    'clientSalesname': [,Validators.required]
  });

    this.toasterService = toasterService;
    this.activatedRoute.params.subscribe((params: Params) => {
        let cl = params['cl'];
        if(cl)
        {
          this.cl = cl;
          this.getClient(cl);
        }
      });
  }

  ngOnInit()
  {
    this.Url = myGlobals.baseUrl;
  }

  getClient(id)
  {
    this.po.getClient(id).subscribe(
      data => {
        if(data.success)
        {
          this.Client     = data.data;
          this.clientPic  = data.data.clientPic
        }
      }
    );
  }

  submitForm(value: any)
  {
    console.log(value);
    console.log(this.poForm);
    this.poFormSubmitted = true;
    if( !this.poForm.valid )
    {
      return false;
    }
    value['cl'] = this.cl;
    value['clientPic'] = this.clientPic;
    this.toasterService.pop('info',' Loading...', '' );
    this.po.updateClient(value).subscribe(
      data => {
        if(data.success)
        {
            this.poFormSubmitted = false;
            this.toasterService.clear();
            this.toasterService.pop('success', data.success, '' );
        }
        else
        {
          this.toasterService.clear();
          this.toasterService.pop('error', data.error, '' );
        }
      },
   );
  }
  updateProfileimage()
  {
    let fi = this.fileInput.nativeElement;
    if (fi.files && fi.files[0])
    {
      this.showImageUploading	= true;
      let fileToUpload = fi.files[0];
      if (fileToUpload.type.indexOf('image') === -1)
      {
        this.showImageUploading	= false;
      }

      this.po.upload(fileToUpload ,'client').subscribe(
        response => {
          setTimeout(function() {
            this.showhidemsg2 = false;
          }.bind(this), 3000);
          this.showImageUploading = false;
          if(response.success)
          {
            this.clientPic 	= response.fileName;
          }
        },
        err => {
          this.showImageUploading		 	= false;
        }
      );
    }
  }

}
