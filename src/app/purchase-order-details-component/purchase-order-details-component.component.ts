import { OnInit } from '@angular/core';
import { Component, Input, Output, EventEmitter } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { ViewChild, ElementRef, AfterViewInit } from '@angular/core';
import { FormArray , FormControl , FormBuilder, FormGroup , Validators } from '@angular/forms';
import {ToasterModule, ToasterService} from 'angular2-toaster';
declare var jQuery: any;
import * as myGlobals from '../shared/globals';
import { PoService }    from '../purchase-orders/po.service';

@Component({
  selector: 'app-purchase-order-details-component',
  templateUrl: './purchase-order-details-component.component.html',
  styleUrls: ['./purchase-order-details-component.component.css'],
  providers:[PoService]
})

export class PurchaseOrderDetailsComponentComponent implements OnInit {

  PurchaseOrderDetails = {};
  PurchaseOrderComments = [];
  private toasterService: ToasterService;
  @Input() orderid;
  @ViewChild('comment') comment;
  comentLoading : boolean = true;
  @ViewChild('fileInput') fileInput: ElementRef;
  showImageUploading : boolean = false;
  FileNames = [];
  baseUrl = '';

  constructor( private router: Router , toasterService: ToasterService , private fb: FormBuilder , private activatedRoute: ActivatedRoute , private po: PoService )
  {
    this.toasterService = toasterService;
  }

  ngOnInit()
  {
    if(this.orderid != '')
    {
      this.getPurchaseOrderDetails();
      this.getPurchaseOrderComments();
    }
    this.baseUrl = myGlobals.baseUrl;
  }

  ngAfterViewInit()
  {
  }

  getPurchaseOrderDetails()
  {
    this.po.getPurchaseOrderDetails(this.orderid).subscribe(
      data => {
        if(data.success)
        {
          this.PurchaseOrderDetails = data.data;
        }
      }
    );
  }

  addPurchaseOrderComments()
  {
     if(this.comment.nativeElement.value == '')
     {
       jQuery(this.comment.nativeElement).parent('.form-group').addClass('has-error');
       return false;
     }
     else
     {
       jQuery(this.comment.nativeElement).parent('.form-group').removeClass('has-error');
     }
    var tkn = localStorage.getItem('AppToken');
    var token = JSON.parse(tkn);

    var value = {};
    value['pomsgComment'] = this.comment.nativeElement.value;
    value['pomsgPOld'] = this.orderid;
    value['pomsgAddedBy'] = token.userId;
    value['files']   = this.FileNames;

    this.po.addPurchaseOrderComments(value).subscribe(
      data => {
        if(data.success)
        {
          this.comment.nativeElement.value = '';
          this.toasterService.pop('success',data.success);
          this.getPurchaseOrderComments();
          this.FileNames = [];
        }
      }
    );

  }

  getPurchaseOrderComments()
  {
    this.comentLoading = true;
    this.po.getPurchaseOrderComments(this.orderid).subscribe(
      data => {
        if(data.success)
        {
          this.comentLoading = false;
          this.PurchaseOrderComments = data.data;
        }
      }
    );
  }

  fileupload()
  {
    jQuery(this.fileInput.nativeElement).trigger('click');
  }

   handleFileUpload(event)
   {
     let fi = this.fileInput.nativeElement;
     if (fi.files && fi.files[0])
     {
       this.showImageUploading	= true;
       let fileToUpload = fi.files[0];
       this.po.handleFileUpload(fileToUpload ,'client').subscribe(
         response => {
           this.showImageUploading = false;
           if(response.success)
           {
             this.FileNames.push(response.fileName);
           }
         },
         err => {
           this.showImageUploading		 	= false;
         }
       );
     }
   }
}
