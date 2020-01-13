import { Injectable } from '@angular/core';
import { HttpClient, HttpErrorResponse, HttpHeaders } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { Config } from 'src/app/config';
import { map, catchError } from 'rxjs/operators';

@Injectable({
    providedIn: 'root'
})
export class ConnectionService {

    constructor(
        private http: HttpClient,
        private config: Config
    ) {}

    public postRequest(token: string, url: string, body: any, headers: Object = {}): Observable<any> {
        //create new header object
        const httpOptions = {
            headers: new HttpHeaders()
                .set('Authorization', token)
        };
        //add the headers if any
        for (let index in headers) {
            httpOptions.headers.set(index, headers[index]);
        }
        //connect to the backend and return the repsonse
        return this.http.post(this.config.BASE_SERVER_URL + url, body, httpOptions)
            .pipe(
                map((res) => {
                    return res;
                }),
                catchError(this.handleError)
            );
    }


    public getRequest<T>(token: string, url: string, headers: Object = {}): Observable<T> {
        //create new header object
        const httpOptions = {
            headers: new HttpHeaders()
                .set('Authorization', token)
        };
        //add the headers if any
        for (let index in headers) {
            httpOptions.headers.set(index, headers[index]);
        }
        //connect to the backend and return the repsonse
        return this.http.get<T>(this.config.BASE_SERVER_URL + url, httpOptions)
            .pipe(
                map((res: T) => {
                    return res as T;
                }),
                catchError(this.handleError)
            );
    }

    public deleteRequest<T>(token: string, url: string, headers: Object = {}): Observable<T> {
        //create new header object
        const httpOptions = {
            headers: new HttpHeaders()
                .set('Authorization', token)
        };
        //add the headers if any
        for (let index in headers) {
            httpOptions.headers.set(index, headers[index]);
        }
        //connect to the backend and return the repsonse
        return this.http.delete<T>(this.config.BASE_SERVER_URL + url, httpOptions)
            .pipe(
                map((res: T) => {
                    return res as T;
                }),
                catchError(this.handleError)
            );
    }

    private handleError(error: HttpErrorResponse) {
        if (error.error instanceof ErrorEvent) {
            // A client-side or network error occurred.
            console.error('An error occurred:', error.error.message);
        } else {
            // A Server error occured
            console.error(
                `Backend returned code ${error.status}, ` +
                `body was: ${error.error.message}`);
        }
        //temp console log the erro to get the full message
        console.log(error);
        //all the meet master messages will be in this format
        return throwError(error.error.message);
    }
}
