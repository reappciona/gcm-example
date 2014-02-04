package kr.appkr.gcm_example;

import android.annotation.TargetApi;
import android.app.IntentService;
import android.app.Notification;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.graphics.BitmapFactory;
import android.os.Build;
import android.os.Bundle;
import android.support.v4.app.NotificationCompat;

import com.google.android.gms.gcm.GoogleCloudMessaging;

public class GcmIntentService extends IntentService {

	public static final int NOTIFICATION_ID = 123456789;
    public GcmIntentService() {
        super("GcmIntentService");
    }

    @Override
    protected void onHandleIntent(Intent intent) {
        Bundle extras = intent.getExtras();
        GoogleCloudMessaging gcm = GoogleCloudMessaging.getInstance(this);
        String messageType = gcm.getMessageType(intent);

        if (!extras.isEmpty()) { // has effect of unparcelling Bundle
        	/*
             * Filter messages based on message type. Since it is likely that GCM
             * will be extended in the future with new message types, just ignore
             * any message types you're not interested in, or that you don't
             * recognize.
             */
        	
        	//MESSAGE_TYPE_SEND_ERROR
            if (GoogleCloudMessaging.MESSAGE_TYPE_SEND_ERROR.equals(messageType)) {
            //MESSAGE_TYPE_DELETED
            } else if (GoogleCloudMessaging.MESSAGE_TYPE_DELETED.equals(messageType)) {
            // If it's a regular GCM message, do some work.
            } else if (GoogleCloudMessaging.MESSAGE_TYPE_MESSAGE.equals(messageType)) {
            	// Post notification of received message.
            	startNotify(extras);
            }
        }
        GcmBroadcastReceiver.completeWakefulIntent(intent);
    }
    
    private void startNotify(Bundle bundle){
    	if(Build.VERSION.SDK_INT >= Build.VERSION_CODES.JELLY_BEAN){
    		jelly(bundle);
    	}else{
    		base(bundle);
    	}
    }
    
    private void base(Bundle bundle) {
    	
    	PendingIntent pIntent = PendingIntent.getActivity(
    			getApplicationContext(), 0, new Intent(getApplicationContext(),
    					MainActivity.class),
    					PendingIntent.FLAG_UPDATE_CURRENT);
    	
		NotificationManager manager = (NotificationManager) getSystemService(Context.NOTIFICATION_SERVICE);
		
		Notification noti = new NotificationCompat.Builder(getApplicationContext())
		.setSmallIcon(R.drawable.ic_launcher)
		.setLargeIcon(BitmapFactory.decodeResource(this.getResources(), R.drawable.ic_launcher))
		.setTicker(bundle.getString("ticker"))
		.setContentTitle(bundle.getString("title"))
		.setContentText(bundle.getString("message"))
		.setAutoCancel(true)
		.setDefaults(Notification.DEFAULT_VIBRATE | Notification.DEFAULT_SOUND)
		.setContentIntent(pIntent)
		.build();
		noti.flags |= Notification.FLAG_SHOW_LIGHTS;
		manager.notify(NOTIFICATION_ID, noti);
    }
    
    @TargetApi(Build.VERSION_CODES.JELLY_BEAN)
	private void jelly(Bundle bundle){
    	
    	PendingIntent pIntent = PendingIntent.getActivity(
				getApplicationContext(), 0, new Intent(getApplicationContext(),
						MainActivity.class),
						PendingIntent.FLAG_UPDATE_CURRENT);
    	
		NotificationManager manager = (NotificationManager) getSystemService(NOTIFICATION_SERVICE);
		
		Notification noti = new Notification.Builder(this)
		.setTicker(bundle.getString("ticker"))
		.setContentTitle(bundle.getString("title"))
		.setContentText(bundle.getString("message"))
		.setLargeIcon(BitmapFactory.decodeResource(getResources(), R.drawable.ic_launcher))
		.setSmallIcon(R.drawable.ic_launcher)
		.setAutoCancel(true)
		.setContentIntent(pIntent)
		.setDefaults(Notification.DEFAULT_VIBRATE | Notification.DEFAULT_SOUND)
		.setStyle(new Notification.BigTextStyle().bigText(bundle.getString("message")))
		.build();
		noti.flags |= Notification.FLAG_SHOW_LIGHTS;
		manager.notify(NOTIFICATION_ID, noti);
	}
    
}
