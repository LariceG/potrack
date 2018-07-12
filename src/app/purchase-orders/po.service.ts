import { Injectable }              from '@angular/core';
import {HttpModule, Http,Response} from '@angular/http';
import { Headers, RequestOptions } from '@angular/http';
import { HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/operator/catch';
import 'rxjs/add/operator/map';
import * as myGlobals from '../shared/globals';

@Injectable()
export class PoService {

  http: Http;
  returnCommentStatus:Object = [];
  token = {};

  constructor(public _http: Http)
  {
      this.http = _http;
  }

  addPo(value: any)
  {
    let headers = new Headers();
    headers.append('Content-Type','application/x-www-form-urlencoded');
    return this.http.post(myGlobals.baseUrl+'api/add-po/',value, { headers }).map(
          (res: Response) => res.json() || {});
  }

  updateClient(value : any)
  {
    let headers = new Headers();
    headers.append('Content-Type','application/x-www-form-urlencoded');
    return this.http.post(myGlobals.baseUrl+'api/update-client/',value, { headers }).map(
          (res: Response) => res.json() || {});
  }

  getClients()
  {
    return this.http.get(myGlobals.baseUrl+'api/get-clients/').map(
          (res: Response) => res.json() || {});
  }

  getClient(id)
  {
    return this.http.get(myGlobals.baseUrl+'api/get-client/'+id).map(
          (res: Response) => res.json() || {});
  }

  getPurchaseOrdes(value)
  {
    if(value['userId'])
    var url = myGlobals.baseUrl+'api/get-purchase-orders/'+value['page']+'/'+value['perpage']+'/'+value['type']+'/'+value['userId'];
    else
    var url = myGlobals.baseUrl+'api/get-purchase-orders/'+value['page']+'/'+value['perpage']+'/'+value['type'];

    return this.http.get(url).map(
          (res: Response) => res.json() || {});
  }

  getPurchaseOrderDetails(orderid)
  {
    return this.http.get(myGlobals.baseUrl+'api/purchase-order-details/'+orderid).map(
          (res: Response) => res.json() || {});
  }

  ChangePoStatus(orderId,status)
  {
    return this.http.get(myGlobals.baseUrl+'api/change-po-status/'+orderId+'/'+status).map(
          (res: Response) => res.json() || {});
  }


  addStore(value)
  {
    let headers = new Headers();
    headers.append('Content-Type','application/x-www-form-urlencoded');
    headers.append('ApiKey',this.token['apiKey'])
    return this.http.post(myGlobals.baseUrl+'api/addStoreUser/',value, { headers }).map(
          (res: Response) => res.json() || {});
  }


  storeDetail(value)
  {
    let headers = new Headers();
    return this.http.get(myGlobals.baseUrl+'api/storeDetails/storeid/'+value,{headers}).map(
          (res: Response) => res.json() || {});
  }

  updateStore(value)
  {
    let headers = new Headers();
    headers.append('Content-Type','application/x-www-form-urlencoded');
    return this.http.put(myGlobals.baseUrl+'api/updateStoreUserDetail/',value, { headers }).map(
          (res: Response) => res.json() || {});
  }

  update(value)
  {
    let headers = new Headers();
    headers.append('Content-Type','application/x-www-form-urlencoded');
    return this.http.put(myGlobals.baseUrl+'api/update/',value, { headers }).map(
          (res: Response) => res.json() || {});
  }

  updatepost(value)
  {
    let headers = new Headers();
    headers.append('Content-Type','application/x-www-form-urlencoded');
    return this.http.post(myGlobals.baseUrl+'api/update-fun/',value, { headers }).map(
          (res: Response) => res.json() || {});
  }

  deleteStore(value)
  {
    let headers = new Headers();
    headers.append('Content-Type','application/x-www-form-urlencoded');
    return this.http.put(myGlobals.baseUrl+'api/activeUserStatus/',value, { headers }).map(
          (res: Response) => res.json() || {});
  }

  insert(value)
  {
    let headers = new Headers();
    headers.append('Content-Type','application/x-www-form-urlencoded');
    return this.http.post(myGlobals.baseUrl+'api/insert/',value, { headers }).map(
          (res: Response) => res.json() || {});
  }

  delete(value)
  {
    return this.http.get(myGlobals.baseUrl+'api/delete/'+value['id']+'/'+value['type']).map(
          (res: Response) => res.json() || {});
  }

  get(value)
  {
    let headers = new Headers();
    return this.http.get(myGlobals.baseUrl+'api/get/'+value,{headers}).map(
          (res: Response) => res.json() || {});
  }

  // upload(fileToUpload: any)
  // {
  //   let input 	= new FormData();
  //   let headers = new Headers();
  //   input.append("file", fileToUpload);
  //   return this.http.post(myGlobals.baseUrl+'api/upload-image/',input, { headers }).map((res: Response) => res.json() || {});
  // }
  //

  upload(fileToUpload: any , type: any)
	{
		let input 	= new FormData();
		let headers = new Headers();
		input.append("file", fileToUpload);
    input.append("type", type);
		return this.http.post(myGlobals.baseUrl+'api/upload-image/',input, { headers }).map((res: Response) => res.json() || {});
	}

   handleFileUpload(fileToUpload: any , type: any)
   {
      let input 	= new FormData();
      let headers = new Headers();
      input.append("file", fileToUpload);
      input.append("type", type);
      return this.http.post(myGlobals.baseUrl+'api/upload-file/',input, { headers }).map((res: Response) => res.json() || {});
   }

  submitCat(value)
  {
    let headers = new Headers();
    headers.append('Content-Type','application/x-www-form-urlencoded');
    return this.http.post(myGlobals.baseUrl+'api/submit-cat/',value, { headers }).map(
          (res: Response) => res.json() || {});
  }

  getAdpmOrders(adpm)
  {
    let headers = new Headers();
    return this.http.get(myGlobals.baseUrl+'api/getadpmorders/'+adpm, { headers }).map(
          (res: Response) => res.json() || {});
  }

  getPortalOrders(type,id)
  {
    if(id == '')
    var url  = myGlobals.baseUrl+'api/get-portal-orders/'+type;
    else
    var url  = myGlobals.baseUrl+'api/get-portal-orders/'+type+'/'+id;

    return this.http.get(url).map((res: Response) => res.json() || {});
  }

  getAdpmStores(apdm)
  {
    return this.http.get(myGlobals.baseUrl+'api/getAdpmStores/'+apdm).map(
          (res: Response) => res.json() || {});
  }

  apdmUserListing(page,perpage)
  {
    return this.http.get(myGlobals.baseUrl+'api/apdmUserListing/'+page+'/'+perpage).map(
          (res: Response) => res.json() || {});
  }

  getdashboard(url,id)
  {
    let headers = new Headers();
    return this.http.get(myGlobals.baseUrl+'api/'+url+'/'+id,{headers}).map(
          (res: Response) => res.json() || {});
  }

  orderDetails(v)
  {
    let headers = new Headers();
    return this.http.get(myGlobals.baseUrl+'api/order-details/'+v, { headers }).map(
          (res: Response) => res.json() || {});
  }

  chnagePassword(v)
  {
    let headers = new Headers();
    headers.append('Content-Type','application/x-www-form-urlencoded');
    return this.http.post(myGlobals.baseUrl+'api/change-password/',v, { headers }).map(
          (res: Response) => res.json() || {});
  }

  getClientDetails(id)
  {
    let headers = new Headers();
    return this.http.get(myGlobals.baseUrl+'api/client-details/'+id, { headers }).map(
          (res: Response) => res.json() || {});
  }

  getDashbordInfo(type,userType)
  {
    let headers = new Headers();
    return this.http.get(myGlobals.baseUrl+'api/dashboard-info/'+type+'/'+userType, { headers }).map(
          (res: Response) => res.json() || {});
  }

  addPurchaseOrderComments(v)
  {
    let headers = new Headers();
    headers.append('Content-Type','application/x-www-form-urlencoded');
    return this.http.post(myGlobals.baseUrl+'api/pomsginsert/',v, { headers }).map(
          (res: Response) => res.json() || {});
  }

  getPurchaseOrderComments(id)
  {
    let headers = new Headers();
    return this.http.get(myGlobals.baseUrl+'api/get-pomsg/'+id, { headers }).map(
          (res: Response) => res.json() || {});
  }

}
