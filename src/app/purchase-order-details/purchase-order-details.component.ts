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
  selector: 'app-purchase-order-details',
  templateUrl: './purchase-order-details.component.html',
  styleUrls: ['./purchase-order-details.component.css'],
  providers:[PoService]
})

export class PurchaseOrderDetailsComponent implements OnInit {

  PurchaseOrderDetails = {};
  clientType = 'new';
  poForm : FormGroup;
  poFormSubmitted;
  private toasterService: ToasterService;
  Clients = [];
  PurchaseOrdes = [];
  total;
  perpage = 10;
  page = 1;
  orderid;

    constructor( private router: Router , toasterService: ToasterService , private fb: FormBuilder , private activatedRoute: ActivatedRoute , private po: PoService )
    {
        this.toasterService = toasterService;
      //   this.poForm = fb.group({
      //   'clientId' : ['',Validators.required],
      //   'clientFirstName' : ['',Validators.required],
      //   'clientLastName': [,Validators.required],
      //   'clientCompany': [,Validators.required],
      //   'clientTelephone': [,Validators.required],
      //   'clientEmail': [,Validators.required],
      //   'clientBillingAddress': [,Validators.required],
      //   'clientDeliveryAddress': [,Validators.required],
      //   'clientCity': [,Validators.required],
      //   'clientCountry': [,Validators.required],
      //   'clientPostal': [,Validators.required],
      //   'orderTelephone': [,Validators.required],
      //   'orderPostal': [,Validators.required],
      //   'orderDueDate': [,Validators.required],
      //   'orderDescription': [,Validators.required],
      //   'orderDeliveryAddress': [,Validators.required],
      // });
    }

    ngOnInit()
    {
      this.activatedRoute.params.subscribe((params: Params) => {
          this.orderid = params['orderid'];
        });
    }

    ngAfterViewInit()
    {

    }

    getPurchaseOrderDetails(orderid)
    {
      this.po.getPurchaseOrderDetails(orderid).subscribe(
        data => {
          if(data.success)
          {
            this.PurchaseOrderDetails = data.data;
          }
        }
      );
    }

  }
