import { OnInit } from '@angular/core';
import { Component, Input, Output, EventEmitter } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { ViewChild, ElementRef, AfterViewInit } from '@angular/core';
import { FormArray , FormControl , FormBuilder, FormGroup , Validators } from '@angular/forms';
import {ToasterModule, ToasterService} from 'angular2-toaster';
declare var jQuery: any;
import * as myGlobals from '../shared/globals';
import { PoService }    from '../purchase-orders/po.service';
import { orderItems } from '../shared/data-model';


@Component({
  selector: 'app-po-edit',
  templateUrl: './po-edit.component.html',
  styleUrls: ['./po-edit.component.css'],
  providers:[PoService]
})


export class PoEditComponent implements OnInit {

  PurchaseOrderDetails = {};
  poFormEdit : FormGroup;
  poFormEditSubmitted = false;
  private toasterService: ToasterService;
  Clients = [];
  Client = {};
  PurchaseOrdes = [];
  token = {};
  Url = '';
  @Input() poToEdit;
  clientType = 'new';
  clientPicExist  = '';
  clientPic = '';
  @Output() onSuccess: EventEmitter<any> = new EventEmitter<any>();

  constructor( private router: Router , toasterService: ToasterService , private fb: FormBuilder , private activatedRoute: ActivatedRoute , private po: PoService )
  {
      this.toasterService = toasterService;
      this.poFormEdit = fb.group({
      // 'orderTelephone': [,Validators.required],
      // 'orderPostal': [,Validators.required],
      'orderDueDate': [,Validators.required],
      'orderDescription': [,Validators.required],
      'orderDeliveryAddress': [,Validators.required],
      'orderTotal' : [,Validators.required],
      // 'orderSalesPerson' : [,Validators.required],
      'items' : this.fb.array([ this.initOrderItems() ])
    });

    this.poFormEdit.controls.items.valueChanges.subscribe((change) => {
      console.log(this.poFormEdit.controls.items);
      var amtt = 0;
      for (let i = 0; i < change.length; i++)
      {
        var amt = (change[i].itemQuantity != '' ? change[i].itemQuantity : 0) * (change[i].itemPrice != '' ? change[i].itemPrice : 0) ;
        this.poFormEdit.controls.items['controls'][i].controls.itemAmount.setValue(amt , {onlySelf: true});
        amtt = amtt + amt;
      }
      this.poFormEdit.controls.orderTotal.setValue(amtt , {onlySelf: true});

    });

  }


  ngOnInit()
  {
    if(this.poToEdit)
    this.editPo(this.poToEdit);

    let tkn    = localStorage.getItem('AppToken');
    this.token  = JSON.parse(tkn);
    this.Url = myGlobals.baseUrl;
  }


    editPo(orderid)
    {
      this.po.getPurchaseOrderDetails(orderid).subscribe(
        data => {
          if(data.success)
          {
            this.PurchaseOrderDetails = data.data;
            this.setOrderItems(this.PurchaseOrderDetails['items']);
          }
        }
      );
    }

    setOrderItems(addresses: orderItems[])
    {
      const addressFGs = addresses.map(orderItems => this.fb.group(orderItems));
      const addressFormArray = this.fb.array(addressFGs);
      this.poFormEdit.setControl('items', addressFormArray);
      this.detect()
    }

    detect()
    {
      this.poFormEdit.controls.items.valueChanges.subscribe((change) => {
        console.log(this.poFormEdit.controls.items);
        var amtt = 0;
        for (let i = 0; i < change.length; i++)
        {
          var amt = (change[i].itemQuantity != '' ? change[i].itemQuantity : 0) * (change[i].itemPrice != '' ? change[i].itemPrice : 0) ;
          this.poFormEdit.controls.items['controls'][i].controls.itemAmount.setValue(amt , {onlySelf: true});
          amtt = amtt + amt;
        }
        this.poFormEdit.controls.orderTotal.setValue(amtt , {onlySelf: true});
      });
    }

    submitForm(value: any)
    {
      this.poFormEditSubmitted = true;

      if( !this.poFormEdit.valid )
      {
        return false;
      }

      value['poId'] = this.poToEdit;

      // if(this.clientType = 'new')
      // {
      //   value['clientPic'] = this.clientPic;
      // }
      // else
      // {
      //   value['clientPic'] = this.clientPicExist;
      // }

      this.toasterService.pop('info','Updating...', '' );
      this.po.addPo(value).subscribe(
        data => {
          if(data.success)
          {
            this.toasterService.clear();
            this.onSuccess.emit(data);
          }
          else
          {
            this.onSuccess.emit(data);
          }
        },
        err => {
      }
     );
    }

    initOrderItems()
    {
        return this.fb.group({
            itemNo: ['', Validators.required],
            itemName: ['', [Validators.required ]],
            itemDescription: ['', Validators.required],
            itemQuantity: ['', Validators.required],
            itemPrice: ['', Validators.required],
            itemAmount: ['', Validators.required]
        });
    }

    addOrderItem()
    {
      const control = <FormArray>this.poFormEdit.controls['items'];
      control.push(this.initOrderItems());
    }

    removeOrderItem(i)
    {
      const control = <FormArray>this.poFormEdit.controls['items'];
      control.removeAt(i);
    }


}
