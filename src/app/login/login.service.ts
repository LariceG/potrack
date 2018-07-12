import { Injectable }              from '@angular/core';
import {HttpModule, Http,Response} from '@angular/http';
import { Headers, RequestOptions } from '@angular/http';
import { HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/operator/catch';
import 'rxjs/add/operator/map';
import * as myGlobals from '../shared/globals';

@Injectable()
export class LoginService {

  http: Http;
  returnCommentStatus:Object = [];
  token;

  constructor(public _http: Http)
  {
      this.http = _http;
      let tkn = localStorage.getItem('ppsPortalToken');
      this.token = JSON.parse(tkn);
  }

  login(value: any)
  {
    let headers = new Headers();
    headers.append('Content-Type','application/x-www-form-urlencoded');
    return this.http.post(myGlobals.baseUrl+'api/login/',value, { headers }).map(
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

  listStores(value)
  {
    let headers = new Headers();
    headers.append('ApiKey',this.token['apiKey'])
    return this.http.get(myGlobals.baseUrl+'api/storeUserListing/page/'+value['page']+'/perpage/'+value['perpage'], { headers }).map(
          (res: Response) => res.json() || {});
  }

  storeDetail(value)
  {
    let headers = new Headers();
    headers.append('ApiKey',this.token['apiKey'])
    return this.http.get(myGlobals.baseUrl+'api/storeDetails/storeid/'+value,{headers}).map(
          (res: Response) => res.json() || {});
  }

  updateStore(value)
  {
    let headers = new Headers();
    headers.append('Content-Type','application/x-www-form-urlencoded');
    headers.append('ApiKey',this.token['apiKey'])
    return this.http.put(myGlobals.baseUrl+'api/updateStoreUserDetail/',value, { headers }).map(
          (res: Response) => res.json() || {});
  }

  update(value)
  {
    let headers = new Headers();
    headers.append('Content-Type','application/x-www-form-urlencoded');
    headers.append('ApiKey',this.token['apiKey'])
    return this.http.put(myGlobals.baseUrl+'api/update/',value, { headers }).map(
          (res: Response) => res.json() || {});
  }

  updatepost(value)
  {
    let headers = new Headers();
    headers.append('Content-Type','application/x-www-form-urlencoded');
    headers.append('ApiKey',this.token['apiKey'])
    return this.http.post(myGlobals.baseUrl+'api/update-fun/',value, { headers }).map(
          (res: Response) => res.json() || {});
  }

  deleteStore(value)
  {
    let headers = new Headers();
    headers.append('Content-Type','application/x-www-form-urlencoded');
    headers.append('ApiKey',this.token['apiKey'])
    return this.http.put(myGlobals.baseUrl+'api/activeUserStatus/',value, { headers }).map(
          (res: Response) => res.json() || {});
  }

  insert(value)
  {
    let headers = new Headers();
    headers.append('Content-Type','application/x-www-form-urlencoded');
    headers.append('ApiKey',this.token['apiKey'])
    return this.http.post(myGlobals.baseUrl+'api/insert/',value, { headers }).map(
          (res: Response) => res.json() || {});
  }

  delete(value)
  {
    let headers = new Headers();
    headers.append('Content-Type','application/x-www-form-urlencoded');
    headers.append('ApiKey',this.token['apiKey'])
    return this.http.delete(myGlobals.baseUrl+'api/delete/id/'+value['id']+'/type/'+value['type'], { headers }).map(
          (res: Response) => res.json() || {});
  }

  get(value)
  {
    let headers = new Headers();
    headers.append('ApiKey',this.token['apiKey'])
    return this.http.get(myGlobals.baseUrl+'api/get/'+value,{headers}).map(
          (res: Response) => res.json() || {});
  }

  upload(fileToUpload: any)
  {
    let input 	= new FormData();
    let headers = new Headers();
    input.append("file", fileToUpload);
    headers.append('ApiKey',this.token['apiKey'])
    return this.http.post(myGlobals.baseUrl+'api/upload-image/',input, { headers }).map((res: Response) => res.json() || {});
  }

  submitCat(value)
  {
    let headers = new Headers();
    headers.append('Content-Type','application/x-www-form-urlencoded');
    headers.append('ApiKey',this.token['apiKey']);
    return this.http.post(myGlobals.baseUrl+'api/submit-cat/',value, { headers }).map(
          (res: Response) => res.json() || {});
  }

  getAdpmOrders(adpm)
  {
    let headers = new Headers();
    headers.append('ApiKey',this.token['apiKey']);
    return this.http.get(myGlobals.baseUrl+'api/getadpmorders/'+adpm, { headers }).map(
          (res: Response) => res.json() || {});
  }

  getPortalOrders(type,id)
  {
    if(id == '')
    var url  = myGlobals.baseUrl+'api/get-portal-orders/'+type;
    else
    var url  = myGlobals.baseUrl+'api/get-portal-orders/'+type+'/'+id;

    let headers = new Headers();
    headers.append('ApiKey',this.token['apiKey']);
    return this.http.get(url, { headers }).map((res: Response) => res.json() || {});
  }

  getAdpmStores(apdm)
  {
    let headers = new Headers();
    headers.append('ApiKey',this.token['apiKey']);
    return this.http.get(myGlobals.baseUrl+'api/getAdpmStores/'+apdm, { headers }).map(
          (res: Response) => res.json() || {});
  }

  apdmUserListing(page,perpage)
  {
    let headers = new Headers();
    headers.append('ApiKey',this.token['apiKey']);
    return this.http.get(myGlobals.baseUrl+'api/apdmUserListing/'+page+'/'+perpage,{headers}).map(
          (res: Response) => res.json() || {});
  }

  getdashboard(url,id)
  {
    let headers = new Headers();
    headers.append('ApiKey',this.token['apiKey']);
    return this.http.get(myGlobals.baseUrl+'api/'+url+'/'+id,{headers}).map(
          (res: Response) => res.json() || {});
  }

  orderDetails(v)
  {
    let headers = new Headers();
    headers.append('ApiKey',this.token['apiKey'])
    return this.http.get(myGlobals.baseUrl+'api/order-details/'+v, { headers }).map(
          (res: Response) => res.json() || {});
  }

  chnagePassword(v)
  {
    let headers = new Headers();
    headers.append('Content-Type','application/x-www-form-urlencoded');
    headers.append('ApiKey',this.token['apiKey'])
    return this.http.post(myGlobals.baseUrl+'api/change-password/',v, { headers }).map(
          (res: Response) => res.json() || {});
  }


}
