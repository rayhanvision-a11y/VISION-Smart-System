allprojects {
    repositories {
        google()
        mavenCentral()
    }
    extra["compileSdkVersion"] = 36
    extra["targetSdkVersion"] = 36
}

// Redirect Gradle output to mobile_app/build, where `flutter build apk` and
// .github/workflows/build_apk.yml both look for the artifacts. Without this the
// APK lands in android/app/build and the Flutter tool reports
// "Gradle build failed to produce an .apk file" even on a successful build.
val newBuildDir: Directory = rootProject.layout.buildDirectory.dir("../../build").get()
rootProject.layout.buildDirectory.value(newBuildDir)

subprojects {
    project.layout.buildDirectory.value(newBuildDir.dir(project.name))
}

subprojects {
    afterEvaluate {
        if (project.extensions.findByName("android") != null) {
            val android = project.extensions.getByName("android") as com.android.build.gradle.BaseExtension
            android.compileSdkVersion(36)
        }
    }
}

subprojects {
    project.evaluationDependsOn(":app")
}

tasks.register<Delete>("clean") {
    delete(rootProject.layout.buildDirectory)
}
