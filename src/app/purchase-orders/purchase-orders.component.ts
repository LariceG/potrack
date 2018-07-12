import { OnInit } from '@angular/core';
import { Component, Input, Output, EventEmitter } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { ViewChild, ElementRef, AfterViewInit } from '@angular/core';
import { FormArray , FormControl , FormBuilder, FormGroup , Validators , AbstractControl , ValidationErrors } from '@angular/forms';
import { ToasterModule, ToasterService } from 'angular2-toaster';
declare var jQuery: any;
import * as myGlobals from '../shared/globals';
import { PoService }    from './po.service';


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
  selector: 'app-purchase-orders',
  templateUrl: './purchase-orders.component.html',
  styleUrls: ['./purchase-orders.component.css'],
  providers:[PoService]
})

export class PurchaseOrdersComponent implements OnInit {

clientType = 'new';
poForm : FormGroup;
clForm : FormGroup;
poFormSubmitted;
clFormSubmitted;
private toasterService: ToasterService;
Clients = [];
Client = {};
PurchaseOrdes = [];
total;
perpage = 10;
page = 1;
token = {};
clientPic = '';
clientPic2 = '';
clientPicExist = '';
showImageUploading : boolean = false;
showImageUploading2 : boolean = false;
responseStatus2:Object	= [];
@ViewChild('fileInput') fileInput: ElementRef;
showhidemsg2 : boolean = false;
oldClient = '';
Url = '';
clientTag = 'google';
expandedPo = '';
PotoDelete = '';
poToEdit = '';
poEdit = false;
orderSelected = '';
statusSelected = '';

  constructor( private router: Router , toasterService: ToasterService , private fb: FormBuilder , private activatedRoute: ActivatedRoute , private po: PoService )
  {
      this.toasterService = toasterService;

      this.clForm = fb.group({
      'clientId' : ['',Validators.required],
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

      this.poForm = fb.group({
      'clientId' : ['',Validators.required],
      'clientFirstName' : ['',Validators.required],
      'clientLastName': [,Validators.required],
      'clientCompany': [,Validators.required],
      'clientTelephone': [,[Validators.required,phnMatcher]],
      'clientEmail': [,[Validators.required,Validators.email]],
      'clientBillingAddress': [,Validators.required],
      // 'clientDeliveryAddress': [,Validators.required],
      'clientCity': [,Validators.required],
      'clientCountry': [,Validators.required],
      'clientPostal': [,Validators.required],
      'clientTag': [''],
      'clientSalesname': [,Validators.required],
      // 'orderTelephone': [,Validators.required],
      // 'orderPostal': [,Validators.required],
      'orderDueDate': [,Validators.required],
      'orderDescription': [,Validators.required],
      'orderDeliveryAddress': [,Validators.required],
      'orderTotal' : [,Validators.required],
      // 'orderSalesPerson' : [,Validators.required],
      'items' : this.fb.array([ this.initOrderItems() ])
    });

    this.poForm.controls.items.valueChanges.subscribe((change) => {
      console.log(this.poForm.controls.items);
      var amtt = 0;
      for (let i = 0; i < change.length; i++)
      {
        var amt = (change[i].itemQuantity != '' ? change[i].itemQuantity : 0) * (change[i].itemPrice != '' ? change[i].itemPrice : 0) ;
        this.poForm.controls.items['controls'][i].controls.itemAmount.setValue(amt , {onlySelf: true});
        amtt = amtt + amt;
      }
      this.poForm.controls.orderTotal.setValue(amtt , {onlySelf: true});

      // const calculateAmount = (payoffs: any[]): number => {
      //   return payoffs.reduce((acc, current) => {
      //      // also handling case when a new pay off is added with amount of null
      //      return acc + parseFloat(current.amount || 0);
      //   }, 0);
      // }
      //
      // console.log(calculateAmount(this.form.controls.payOffs.value));
    });

  }

  ngOnInit()
  {
    this.poForm.controls['clientId'].disable();
    let tkn    = localStorage.getItem('AppToken');
    this.token  = JSON.parse(tkn);
    this.getClients();
    this.getPurchaseOrdes();
    this.Url = myGlobals.baseUrl;

  }


  ngAfterViewInit()
  {
    var thiss = this;
    jQuery('#poeditmodal').on('hidden.bs.modal', function () {
      thiss.poToEdit = '';
      thiss.poEdit = false;
    });

    this.activatedRoute.params.subscribe((params: Params) => {
        let type = params['type'];
        if(type && type == 'add')
        {
          jQuery('#addPo').modal('show');
        }
      });
  }

  clientTypeChange(type)
  {
    this.clientType = type;
    if(type == 'old')
    {
      this.poForm.controls['clientId'].enable();
      this.poForm.controls['clientFirstName'].disable();
      this.poForm.controls['clientLastName'].disable();
      this.poForm.controls['clientCompany'].disable();
      this.poForm.controls['clientTelephone'].disable();
      this.poForm.controls['clientEmail'].disable();
      this.poForm.controls['clientTag'].disable();
      this.poForm.controls['clientSalesname'].disable();
      this.poForm.controls['clientBillingAddress'].disable();
      // this.poForm.controls['clientDeliveryAddress'].disable();
      this.poForm.controls['clientCity'].disable();
      this.poForm.controls['clientCountry'].disable();
      this.poForm.controls['clientPostal'].disable();
    }
    else
    {
      this.poForm.controls['clientId'].disable();
      this.poForm.controls['clientFirstName'].enable();
      this.poForm.controls['clientLastName'].enable();
      this.poForm.controls['clientCompany'].enable();
      this.poForm.controls['clientTelephone'].enable();
      this.poForm.controls['clientEmail'].enable();
      this.poForm.controls['clientTag'].enable();
      this.poForm.controls['clientSalesname'].enable();
      this.poForm.controls['clientBillingAddress'].enable();
      // this.poForm.controls['clientDeliveryAddress'].enable();
      this.poForm.controls['clientCity'].enable();
      this.poForm.controls['clientCountry'].enable();
      this.poForm.controls['clientPostal'].enable();
    }
  }

  getPurchaseOrdes()
  {
    var v = {};
    v['page'] = this.page;
    v['perpage'] = this.perpage;
    if(this.token['userType'] == 1)
    {
      v['type'] = 'forSales';
    }

    else if(this.token['userType'] == 2)
    {
      v['type'] = 'forDesigner';
    }

    else if(this.token['userType'] == 3)
    {
      v['type'] = 'forProduction';
    }

    else if(this.token['userType'] == 4)
    {
      v['type'] = 'forClient';
    }

    console.log(v);

    this.po.getPurchaseOrdes(v).subscribe(
      data => {
        if(data.success)
        {
          this.PurchaseOrdes = data.data.result;
          this.total = data.data.total;
        }
      }
    );
  }

  getClients()
  {
    this.po.getClients().subscribe(
      data => {
        if(data.success)
        {
          this.Clients = data.clients;
        }
      }
    );
  }

  submitForm(value: any)
  {
    this.poFormSubmitted = true;

    if( !this.poForm.valid )
    {
      return false;
    }

    if(this.clientType = 'new')
    {
      value['clientPic'] = this.clientPic;
    }
    else
    {
      value['clientPic'] = this.clientPicExist;
    }

    this.toasterService.pop('info',' Loading...', '' );
    this.po.addPo(value).subscribe(
      data => {
        if(data.success)
        {
            jQuery('#addPo').modal('hide');
            this.getPurchaseOrdes();
            this.poFormSubmitted = false;
            this.toasterService.clear();
            this.toasterService.pop('success', data.success, '' );
            this.poForm.reset();
        }
        else
        {
          this.toasterService.clear();
          this.toasterService.pop('error', data.error, '' );
        }
      },
      err => {
    }
   );
  }


  submitclForm(value: any)
  {
    this.clFormSubmitted = true;

    if( !this.clForm.valid )
    {
      return false;
    }

    value['clientPic'] = this.clientPic2;

    this.toasterService.pop('info',' Loading...', '' );
    this.po.addPo(value).subscribe(
      data => {
        if(data.success)
        {
            jQuery('#addCl').modal('hide');
            this.clFormSubmitted = false;
            this.toasterService.clear();
            this.toasterService.pop('success', data.success, '' );
            this.clForm.reset();
        }
        else
        {
          this.toasterService.clear();
          this.toasterService.pop('error', data.error, '' );
        }
      },
      err => {
    }
   );
  }

  ChangePoStatus(type,orderId,status)
  {
   if(type == 'confirm')
   {
     this.orderSelected = orderId;
     this.statusSelected = status;
     jQuery('#confirm').modal('show');
     return false;
   }
   this.toasterService.pop('info','Loading...', '' );

    this.po.ChangePoStatus(orderId,status).subscribe(
      data => {
        this.toasterService.clear();
        jQuery('#confirm').modal('hide');
        this.getPurchaseOrdes();
        this.toasterService.pop('success',data.data, '' );
      },
      err => {
      }
    );
  }

  // 0 Added
  // 1 Submited for design
  // 2 Started design
  // 3 Design under review
  // 4 Approved
  // 5 Submit for production
  // 6 Production Started
  // 7 production Complete
  // 8 Due Amount
  // 9 Due Complete
  // 10 Delivered
  // 11 Track
  // 12 Delivery

  updateProfileimage()
  {
    let fi = this.fileInput.nativeElement;
    if (fi.files && fi.files[0])
    {
      this.showImageUploading	= true;
      this.showhidemsg2 		= true;
      let fileToUpload = fi.files[0];
      if (fileToUpload.type.indexOf('image') === -1)
      {
        this.responseStatus2['error_msg'] = 'Only images are allowed.';
        this.showImageUploading	= false;
        this.showhidemsg2 		= false;
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
            this.responseStatus2['success_msg'] 	= response.success_msg;
          }
          else
          {
            this.responseStatus2['error_msg'] = response.error_msg;
          }
        },
        err => {
          this.responseStatus2['error_msg'] = 'Something happens wrong. Please try again.';
          this.showImageUploading		 	= false;
        }
      );
    }
  }

  clientChng(event)
  {
    console.log(event.target.value);
    this.po.getClientDetails(event.target.value).subscribe(
      data => {
        if(data.success)
        {
          this.Client = data.data;
          this.clientPicExist = data.data['clientPic'];
        }
      }
    );
  }

  initOrderItems(){
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
    const control = <FormArray>this.poForm.controls['items'];
    control.push(this.initOrderItems());
  }

  removeOrderItem(i)
  {
    const control = <FormArray>this.poForm.controls['items'];
    control.removeAt(i);
  }

  expandPO(orderId)
  {
    if(this.expandedPo == orderId)
    this.expandedPo = '';
    else
    this.expandedPo = orderId;
  }

  deletePoConfirm(orderId)
  {
    this.PotoDelete = orderId;
    jQuery('#podeletemodal').modal('show');
  }

  deletePo()
  {
    let obj = {};
    obj['type']  = 'po';
    obj['id']  = this.PotoDelete;
    this.po.delete(obj).subscribe(
      data => {
        if(data.success)
        {
          this.toasterService.pop('success', data.success , '' );
          this.getPurchaseOrdes();
          jQuery('#podeletemodal').modal('hide');
        }
      },
      err => console.log(err)
   );
  }

  editPo(orderid)
  {
    this.poToEdit = orderid;
    this.poEdit = true;
    jQuery('#poeditmodal').modal('show');
  }

  onSuccess(data)
  {
    console.log(data);
    if(data.success)
    {
      this.poToEdit = '';
      this.poEdit = false;
      this.toasterService.pop('success', data.success , '' );
      jQuery('#poeditmodal').modal('hide');
    }
    else
    {
      this.toasterService.pop('error', data.error , '' );
      jQuery('#poeditmodal').modal('hide');
    }
  }






}
